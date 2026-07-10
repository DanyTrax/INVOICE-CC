<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;

    public string $renderedBody;

    public function __construct(
        public Invoice $invoice,
        public Setting $brand,
        public string $pdfContent,
    ) {
        $service = app(InvoiceService::class);
        $this->renderedSubject = $service->renderEmailPlaceholders(
            $brand->invoice_email_subject ?: 'Cuenta de cobro — {{numero}}',
            $invoice
        );
        $this->renderedBody = $service->renderEmailPlaceholders(
            $brand->invoice_email_body ?: '<p>Adjuntamos su cuenta de cobro.</p>',
            $invoice
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'brand' => $this->brand,
                'bodyHtml' => $this->renderedBody,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $filename = 'cuenta-cobro-'.preg_replace('/[^A-Za-z0-9._-]+/', '-', $this->invoice->number).'.pdf';

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
