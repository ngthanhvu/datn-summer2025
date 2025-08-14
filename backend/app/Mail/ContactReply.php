<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $contact;
    public $adminReply;

    public function __construct(Contact $contact, $adminReply)
    {
        $this->contact = $contact;
        $this->adminReply = $adminReply;
    }

    public function build()
    {
        return $this->subject('Phản hồi liên hệ từ DEVGANG')
            ->view('emails.contact-reply')
            ->with([
                'contact' => $this->contact,
                'adminReply' => $this->adminReply
            ]);
    }
}
