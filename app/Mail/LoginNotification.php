<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $userEmail;
    public string $userRole;
    public string $ipAddress;
    public string $country;
    public string $city;
    public string $loginTime;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $userName,
        string $userEmail,
        string $userRole,
        string $ipAddress,
        string $country,
        string $city,
        string $loginTime
    ) {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->userRole = $userRole;
        $this->ipAddress = $ipAddress;
        $this->country = $country;
        $this->city = $city;
        $this->loginTime = $loginTime;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Login Alert — ' . $this->userName . ' just signed in',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.login_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
