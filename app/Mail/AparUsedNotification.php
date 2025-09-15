<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AparUsedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $penggunaanApar;

    /**
     * Create a new message instance.
     */
    public function __construct($penggunaanApar)
    {
        $this->penggunaanApar = $penggunaanApar;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'APAR dengan kode "' . $this->penggunaanApar->masterApar->kode_apar . '" telah digunakan',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.apar-used',
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
