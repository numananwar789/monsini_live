<?php
namespace App\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $resetTime;
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->resetTime = now()->timezone(config('app.timezone'))->format('D, d M Y h:i:s A T');
    }
    public function build()
    {
        $accountStatus = match ($this->user->admin_status) {
            'not_allow' => 'Pending',
            'allow' => 'Approved',
            default => ucfirst($this->user->admin_status),
        };

        return $this->markdown('emails.password_reset_notification')
            ->with([
                'user' => $this->user,
                'resetTime' => $this->resetTime,
                'accountStatus' => $accountStatus,
            ])
            ->subject('Password Reset Completed');
    }
}
