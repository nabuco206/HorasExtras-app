<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     */
    public function build()
    {
        dd('MMMM');
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Correo de prueba')
                    ->view('emails.test'); // Vista del correo
    }
}
