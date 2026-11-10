<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The invoice email body, and nothing else.
 *
 * Building the message is kept apart from sending it so the PDF rendering,
 * the view choice and the subject can be asserted in a test without an SMTP
 * server. The delivery, the log row and the failure bookkeeping live in
 * App\Jobs\SendInvoiceEmail, which is what actually gets queued.
 */
class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $type  InvoiceMail::TYPE_INVOICE or TYPE_OVERDUE_REMINDER
     */
    public function __construct(
        public Invoice $invoice,
        public string $type = InvoiceMail::TYPE_INVOICE,
        public bool $attachPdf = true,
    ) {
    }

    /** Plain invoice delivery. */
    public const TYPE_INVOICE = 'invoice';

    /** Daily nudge for an invoice past its due date. */
    public const TYPE_OVERDUE_REMINDER = 'overdue_reminder';

    public function build(): self
    {
        $invoice = $this->invoice;
        $customer = $invoice->customer;

        $fromAddress = config('mail.billing_from.address', 'billing@iconicpages.com');
        $fromName = config('mail.billing_from.name', config('app.name', 'Iconicpages'));
        $replyTo = config('mail.billing_from.reply_to', $fromAddress);

        $this->subject($this->subjectLine())
            ->from($fromAddress, $fromName)
            ->to($customer->email, $customer->name);

        if ($replyTo) {
            $this->replyTo($replyTo, $fromName);
        }

        $this->view($this->viewForType(), [
            'invoice' => $invoice,
            'customer' => $customer,
        ]);

        if ($this->attachPdf) {
            $this->attachData($this->renderPdf(), $invoice->getPdfFilename(), [
                'mime' => 'application/pdf',
            ]);
        }

        return $this;
    }

    public function subjectLine(): string
    {
        return $this->type === self::TYPE_OVERDUE_REMINDER
            ? 'Overdue Invoice ' . $this->invoice->invoice_number . ' from Iconicpages'
            : 'Invoice ' . $this->invoice->invoice_number . ' from Iconicpages';
    }

    private function viewForType(): string
    {
        return $this->type === self::TYPE_OVERDUE_REMINDER
            ? 'emails.invoice-overdue-reminder'
            : 'emails.invoice-sent';
    }

    /**
     * The invoice PDF, rendered on demand.
     *
     * The font directory is created here rather than assumed: a fresh checkout
     * has no storage/fonts and DomPDF fails rather than falls back.
     */
    private function renderPdf(): string
    {
        $fontsDir = storage_path('fonts');

        if (! is_dir($fontsDir)) {
            mkdir($fontsDir, 0755, true);
        }

        return PdfFacade::setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ])->loadView('invoices.pdf', ['invoice' => $this->invoice])->output();
    }
}
