<?php

namespace App\Mail;

use App\Models\WnPlusInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WnPlusInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WnPlusInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Attiva il tuo account Welfare Nest Plus',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.wn-plus.invitation',
            with: [
                'invitation' => $this->invitation,
                'activationUrl' => route('wn-plus.invitations.accept', $this->invitation->token),
            ],
        );
    }
}