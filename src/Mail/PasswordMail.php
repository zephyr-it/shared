<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $name;

    public $password;

    public $loginUrl;

    public function __construct(string $name, string $password, string $loginUrl)
    {
        $this->name = $name;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'shared::mail.password',
            with: [
                'name' => $this->name,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }
}
