<?php

namespace App\Mail;

use App\Models\ConsentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsentRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ConsentRequest $consentRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Richiesta di conferma dei consensi Welfare Nest',
        );
    }

    /**
     * Template HTML completo (tabellare, con fallback Outlook) fornito dal
     * cliente: non è un markdown mail component, quindi `view:` e non
     * `markdown:`, altrimenti Laravel lo passerebbe al layout di default.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.consent-request',
            with: [
                'consentRequest' => $this->consentRequest,
                'consentUrl' => route(
                    'consent-requests.show',
                    $this->consentRequest->token
                ),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
