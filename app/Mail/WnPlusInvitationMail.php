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
        // HTML completo e non più markdown: il template Markdown di Laravel non
        // permette l'impaginazione a tabelle e i fallback Outlook necessari
        // all'identità Welfare Nest, gia' adottati per l'email dei consent request.
        return new Content(
            view: 'emails.wn-plus.invitation',
            with: [
                'invitation' => $this->invitation,
                'activationUrl' => route('wn-plus.invitations.accept', $this->invitation->token),
            ],
        );
    }
}
