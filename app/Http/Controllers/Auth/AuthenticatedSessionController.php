<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\LoginNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.custom-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $customer = \App\Models\Customer::where('user_id', auth()->id())->first();

        // ⭐ Block login if customer is NOT allowed
        if ($customer && $customer->cust_status !== 'allow') {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not approved by admin yet.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // 📍 Track IP & geolocation, then notify super admin
        $this->trackLoginAndNotify($request, auth()->user());

        if (auth()->user()->admin_role != 'customer') {
            return redirect()->intended(route('products.index', absolute: false));
        }

        return redirect()->intended('customer/products');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function login(Request $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // 📍 Track IP & geolocation, then notify super admin
        $this->trackLoginAndNotify($request, auth()->user());

        if (auth()->user()->admin_role != 'customer') {
            return redirect()->intended(route('products.index', absolute: false));
        }

        return redirect()->intended('customer/products');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the real client IP address, honouring common proxy headers.
     */
    private function resolveClientIp(Request $request): string
    {
        // Check trusted proxy headers (most accurate behind load-balancers / proxies)
        $forwardedFor = $request->header('X-Forwarded-For');
        if ($forwardedFor) {
            // X-Forwarded-For can be a comma-separated list; the first IP is the client
            $ips = array_map('trim', explode(',', $forwardedFor));
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        $cfIp = $request->header('CF-Connecting-IP'); // Cloudflare
        if ($cfIp && filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }

        $realIp = $request->header('X-Real-IP');
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        // Fallback to Laravel's resolved IP
        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Look up geolocation for a given IP address.
     * Primary:  ip-api.com  (free, no key required, high accuracy)
     * Fallback: ipinfo.io
     *
     * Returns ['country' => '...', 'city' => '...']
     */
    private function resolveGeoLocation(string $ip): array
    {
        $defaultResult = ['country' => 'Unknown', 'city' => 'Unknown'];

        // Local / private IPs cannot be geolocated
        if (
            $ip === '127.0.0.1' ||
            $ip === '::1' ||
            !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            return ['country' => 'Local', 'city' => 'Local'];
        }

        // ── Primary: ip-api.com (most accurate, includes ISP / district info) ──
        try {
            $response = Http::timeout(5)
                ->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,regionName,city,lat,lon,isp,query',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country' => $data['country']    ?? 'Unknown',
                        'city'    => ($data['city'] ?? '') !== ''
                            ? $data['city'] . (isset($data['regionName']) && $data['regionName'] !== $data['city']
                                ? ', ' . $data['regionName']
                                : '')
                            : ($data['regionName'] ?? 'Unknown'),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("ip-api.com lookup failed for IP {$ip}: " . $e->getMessage());
        }

        // ── Fallback: ipinfo.io ──
        try {
            $response = Http::timeout(5)->get("https://ipinfo.io/{$ip}/json");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? 'Unknown',
                    'city'    => $data['city']    ?? 'Unknown',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("ipinfo.io lookup failed for IP {$ip}: " . $e->getMessage());
        }

        return $defaultResult;
    }

    /**
     * Track the login (persist to DB) and send an email alert to the super admin.
     */
    private function trackLoginAndNotify(Request $request, User $user): void
    {
        try {
            $ip      = $this->resolveClientIp($request);
            $geo     = $this->resolveGeoLocation($ip);
            $now     = now();

            // Persist to the users table
            $user->update([
                'login_ip'      => $ip,
                'login_country' => $geo['country'],
                'login_city'    => $geo['city'],
                'last_login_at' => $now,
            ]);

            // Build the notification mail and send it to the super admin
            $superAdminEmail = env('SUPER_ADMIN_NOTIFY_EMAIL', 'numananwar@gmail.com');

            Mail::to($superAdminEmail)->send(new LoginNotification(
                userName:  $user->name,
                userEmail: $user->email,
                userRole:  $user->admin_role ?? 'customer',
                ipAddress: $ip,
                country:   $geo['country'],
                city:      $geo['city'],
                loginTime: $now->timezone('Asia/Karachi')->format('D, d M Y  h:i:s A T'),
            ));

        } catch (\Throwable $e) {
            // Never let tracking/email errors break the login flow
            Log::error('Login tracking/notification failed: ' . $e->getMessage());
        }
    }
}

