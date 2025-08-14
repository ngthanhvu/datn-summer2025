<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Yêu cầu hoàn hàng bị từ chối')
            ->view('emails.return-rejected') // Đúng tên view!
            ->with(['order' => $this->order, 'reason' => $this->reason]);
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
