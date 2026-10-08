<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;

class InvoiceEmailService
{
    /**
     * Send invoice email to customer
     *
     * @param Invoice $invoice
     * @param bool $attachPdf
     * @return void
     */
    public function sendInvoice(Invoice $invoice, bool $attachPdf = true): void
    {
        $customer = $invoice->customer;
        
        if (!$customer->email) {
            throw new \Exception('Customer does not have an email address.');
        }

        $data = [
            'invoice' => $invoice,
            'customer' => $customer,
        ];

        // Avoid SMTP auth failures in local development by default.
        // Set MAIL_FORCE_SMTP=true if you want to test real SMTP locally.
        $mailer = (app()->environment('local') && !filter_var(env('MAIL_FORCE_SMTP', false), FILTER_VALIDATE_BOOL))
            ? 'log'
            : config('mail.default');

        Mail::mailer($mailer)->send('emails.invoice-sent', $data, function ($message) use ($invoice, $customer, $attachPdf) {
            $message->from(config('mail.from.address'), config('mail.from.name', config('app.name', 'CRM')))
                    ->to($customer->email, $customer->name)
                    ->subject('Invoice ' . $invoice->invoice_number . ' from Iconicpages');

            if ($attachPdf) {
                // Ensure storage/fonts directory exists
                $fontsDir = storage_path('fonts');
                if (!is_dir($fontsDir)) {
                    mkdir($fontsDir, 0755, true);
                }
                
                // Configure PDF options before loading view (setOptions expects an array)
                $options = [
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                ];
                
                $pdf = PdfFacade::setOptions($options)->loadView('invoices.pdf', ['invoice' => $invoice]);
                $message->attachData($pdf->output(), $invoice->getPdfFilename(), [
                    'mime' => 'application/pdf',
                ]);
            }
        });
    }

    /**
     * Send overdue reminder email (daily until paid).
     */
    public function sendOverdueReminder(Invoice $invoice, bool $attachPdf = true): void
    {
        $invoice->loadMissing(['customer', 'payments', 'items.service']);

        $customer = $invoice->customer;

        if (!$customer?->email) {
            throw new \Exception('Customer does not have an email address.');
        }

        $data = [
            'invoice' => $invoice,
            'customer' => $customer,
        ];

        $mailer = (app()->environment('local') && !filter_var(env('MAIL_FORCE_SMTP', false), FILTER_VALIDATE_BOOL))
            ? 'log'
            : config('mail.default');

        Mail::mailer($mailer)->send('emails.invoice-overdue-reminder', $data, function ($message) use ($invoice, $customer, $attachPdf) {
            $message->from(config('mail.from.address'), config('mail.from.name', config('app.name', 'Iconicpages')))
                ->to($customer->email, $customer->name)
                ->subject('Overdue Invoice ' . $invoice->invoice_number . ' from Iconicpages');

            if ($attachPdf) {
                $fontsDir = storage_path('fonts');
                if (!is_dir($fontsDir)) {
                    mkdir($fontsDir, 0755, true);
                }

                $options = [
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isFontSubsettingEnabled' => true,
                ];

                $pdf = PdfFacade::setOptions($options)->loadView('invoices.pdf', ['invoice' => $invoice]);
                $message->attachData($pdf->output(), $invoice->getPdfFilename(), [
                    'mime' => 'application/pdf',
                ]);
            }
        });
    }
}
