<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:manage-users');
    // }

    public function index()
    {

        
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        
        $users = User::where("admin_role" , '!=' , 'customer')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'admin_role' => ['required', Rule::in(['superadmin', 'admin', 'editor'])],
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = 'allow'; // Auto-approve new users created by superadmin

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'user_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'admin_role' => ['required', Rule::in(['superadmin', 'admin'])],
        ];
 
        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        // Prepare data for update
        $updateData = [
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'email' => $validated['email'],
            'admin_role' => $validated['admin_role'],
        ];

        // Only update password if it's provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        // Prevent demoting the last superadmin
        if ($user->isSuperAdmin() && $validated['admin_role'] !== 'superadmin') {
            $superadminCount = User::where('admin_role', 'superadmin')->count();
            if ($superadminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Cannot demote the last superadmin');
            }
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Admin user updated successfully');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    public function export()
    {
        // Implement your export logic here
        return response()->download(storage_path('app/users-export.csv'));
    }
}
