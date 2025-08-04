<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactDeleted extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        return $this->subject('Thông báo về liên hệ của bạn - DEVGANG')
            ->view('emails.contact-deleted')
            ->with([
                'contact' => $this->contact
            ]);
    }
}
