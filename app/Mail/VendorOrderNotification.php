<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class VendorOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $emailBody;
    public $styleGroups;
    // public $minSize;
    // public $maxSize;
    public $sizeHeaders;
    public $totalPrice;
    public $vendorPurchaseId;
    public $shipByDate;
    public $orders;

    // public function __construct($emailBody, $styleGroups, $minSize, $maxSize, $totalPrice, $vendorPurchaseId, $shipByDate,$orders)
    // {
    //     $this->emailBody = $emailBody;
    //     $this->styleGroups = $styleGroups;
    //     $this->minSize = $minSize;
    //     $this->maxSize = $maxSize;
    //     $this->totalPrice = $totalPrice;
    //     $this->vendorPurchaseId = $vendorPurchaseId;
    //     $this->shipByDate = $shipByDate;
    //     $this->orders = $orders;
    // }
    
    public function __construct($emailBody, $styleGroups, $sizeHeaders, $totalPrice, $vendorPurchaseId, $shipByDate, $orders)
    {
        $this->emailBody = $emailBody;
        $this->styleGroups = $styleGroups;
        $this->sizeHeaders = $sizeHeaders;
        $this->totalPrice = $totalPrice;
        $this->vendorPurchaseId = $vendorPurchaseId;
        $this->shipByDate = $shipByDate;
        $this->orders = $orders;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order - ' . $this->vendorPurchaseId,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor_order',
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
