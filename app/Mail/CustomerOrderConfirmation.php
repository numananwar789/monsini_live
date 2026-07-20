<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $emailBody;
    public $orders;
    public $vendorMessages;
    public $totalCost;

    public function __construct($emailBody, $orders, $vendorMessages, $totalCost)
    {
        $this->emailBody = $emailBody;
        $this->orders = $orders;
        $this->vendorMessages = $vendorMessages;
        $this->totalCost = $totalCost;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Customer Order Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer_order',
        );
    }


     public function build()
    {
        return $this->view('emails.customer_order')
                    ->subject('Customer Order Confirmation');
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
