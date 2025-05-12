<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\PurchasePoolTier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderFinalisedNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Order $order, public ?PurchasePoolTier $appliedTier)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Purchase Pool Order Has Been Finalised!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.finalised',
            with: [
                'order' => $this->order,
                'user' => $this->order->user,
                'product' => $this->order->product,
                'appliedTier' => $this->appliedTier,
                'initialAmount' => $this->order->initial_amount,
                'finalAmount' => $this->order->final_amount,
                'discountApplied' => $this->order->initial_amount - $this->order->final_amount,
            ],
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
