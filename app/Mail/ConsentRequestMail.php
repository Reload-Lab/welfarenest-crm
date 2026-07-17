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

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.consent-request',
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