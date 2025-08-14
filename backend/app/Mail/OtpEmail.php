<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OtpEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Mã OTP đặt lại mật khẩu')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'user' => $this->user,
            ]);
    }
}
