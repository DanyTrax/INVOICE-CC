<?php

namespace App\Services;

use App\Mail\InvoiceMail;
use App\Models\Associate;
use App\Models\Concept;
use App\Models\Invoice;
use App\Models\Setting;
use App\Settings\GeneralSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    public function nextConsecutive(): int
    {
        $max = (int) Invoice::query()->max('consecutive');

        return $max + 1;
    }

    public function formatNumber(int $consecutive): string
    {
        return sprintf('N0.%02d-%d', 0, $consecutive);
    }

    public function resolveAmount(Concept $concept, Associate $associate): float
    {
        $concept->loadMissing('prices');
        $amount = $concept->amountForCategory($associate->category);

        if ($amount === null) {
            throw new \InvalidArgumentException(
                'No hay valor definido para la categoría «'.$associate->category.'» en el concepto seleccionado.'
            );
        }

        return $amount;
    }

    public function createInvoice(array $data, int $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $associate = Associate::findOrFail($data['associate_id']);
            $concept = Concept::with('prices')->findOrFail($data['concept_id']);
            $consecutive = $this->nextConsecutive();

            return Invoice::create([
                'number' => $this->formatNumber($consecutive),
                'consecutive' => $consecutive,
                'associate_id' => $associate->id,
                'concept_id' => $concept->id,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'total_amount' => $this->resolveAmount($concept, $associate),
                'status' => $data['status'] ?? Invoice::STATUS_DRAFT,
                'created_by_id' => $userId,
            ]);
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        if (! $invoice->isEditable()) {
            throw new \RuntimeException('Solo se pueden editar cuentas de cobro en estado borrador.');
        }

        $associate = Associate::findOrFail($data['associate_id']);
        $concept = Concept::with('prices')->findOrFail($data['concept_id']);

        $invoice->fill([
            'associate_id' => $associate->id,
            'concept_id' => $concept->id,
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'total_amount' => $this->resolveAmount($concept, $associate),
            'status' => $data['status'],
        ]);

        if ($data['status'] === Invoice::STATUS_SENT && ! $invoice->sent_at) {
            $invoice->sent_at = now();
        }
        if ($data['status'] === Invoice::STATUS_PAID && ! $invoice->paid_at) {
            $invoice->paid_at = now();
        }

        $invoice->save();

        return $invoice;
    }

    /**
     * @return \Barryvdh\DomPDF\PDF
     */
    public function makePdf(Invoice $invoice)
    {
        $invoice->load(['associate', 'concept']);
        $brand = Setting::current();

        return Pdf::loadView('pdf.invoice_pdf', [
            'invoice' => $invoice,
            'brand' => $brand,
        ])->setPaper('letter');
    }

    public function pdfBinary(Invoice $invoice): string
    {
        return $this->makePdf($invoice)->output();
    }

    public function sendByEmail(Invoice $invoice): void
    {
        $invoice->load(['associate', 'concept']);
        $associate = $invoice->associate;

        if (! $associate?->email) {
            throw new \RuntimeException('El asociado no tiene correo electrónico configurado.');
        }

        $pdfContent = $this->pdfBinary($invoice);
        $brand = Setting::current();

        $this->configureMailTransport();

        Mail::to($associate->email)->send(new InvoiceMail($invoice, $brand, $pdfContent));

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            $invoice->update([
                'status' => Invoice::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }
    }

    public function renderEmailPlaceholders(string $text, Invoice $invoice): string
    {
        $invoice->loadMissing(['associate', 'concept']);

        $replacements = [
            '{{nombre}}' => $invoice->associate->full_name ?? '',
            '{{documento}}' => $invoice->associate->document_id ?? '',
            '{{numero}}' => $invoice->number,
            '{{concepto}}' => $invoice->concept->name ?? '',
            '{{valor}}' => number_format((float) $invoice->total_amount, 0, ',', '.'),
            '{{vencimiento}}' => $invoice->due_date?->format('d/m/Y') ?? '',
            '{{empresa}}' => Setting::current()->company_name ?? config('app.name'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private function configureMailTransport(): void
    {
        $settings = app(GeneralSettings::class);
        $provider = $settings->mail_provider ?? 'smtp';

        if ($provider === 'zoho') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.zoho.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => $settings->zoho_from_email,
                'mail.mailers.smtp.password' => $settings->zoho_access_token,
                'mail.from.address' => $settings->zoho_from_email,
                'mail.from.name' => $settings->mail_from_name,
            ]);

            return;
        }

        config([
            'mail.default' => $settings->mail_mailer ?: 'smtp',
            'mail.mailers.smtp.host' => $settings->mail_host,
            'mail.mailers.smtp.port' => $settings->mail_port,
            'mail.mailers.smtp.encryption' => $settings->mail_encryption,
            'mail.mailers.smtp.username' => $settings->mail_username,
            'mail.mailers.smtp.password' => $settings->mail_password,
            'mail.from.address' => $settings->mail_from_address,
            'mail.from.name' => $settings->mail_from_name,
        ]);
    }
}
