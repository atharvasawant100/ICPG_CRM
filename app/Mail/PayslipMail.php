<?php

namespace App\Mail;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The payslip email body, and nothing else.
 *
 * Separate from the job that sends it so the PDF rendering and the subject can
 * be asserted without an SMTP server, and so a payslip can be previewed or
 * re-sent from anywhere without going through the queue.
 */
class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payroll $payroll,
        public bool $attachPdf = true,
    ) {
    }

    public function build(): self
    {
        $fromAddress = config('mail.payroll_from.address') ?: config('mail.from.address');
        $fromName = config('mail.payroll_from.name', 'ICPG Payroll');
        $replyTo = config('mail.payroll_from.reply_to');

        $this->subject($this->subjectLine())
            ->from($fromAddress, $fromName)
            ->to($this->payroll->user->email, $this->payroll->user->name);

        if ($replyTo) {
            $this->replyTo($replyTo, $fromName);
        }

        $this->view('emails.payslip', [
            'payroll' => $this->payroll,
            'user' => $this->payroll->user,
        ]);

        if ($this->attachPdf) {
            $this->attachData($this->renderPdf(), $this->filename(), [
                'mime' => 'application/pdf',
            ]);
        }

        return $this;
    }

    public function subjectLine(): string
    {
        return 'Salary Slip - ' . $this->payroll->period_label . ' | ' . $this->payroll->payroll_code;
    }

    public function filename(): string
    {
        return 'payslip-' . $this->payroll->payroll_code . '.pdf';
    }

    /**
     * The payslip PDF, rendered on demand.
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
        ])->loadView('payroll.payslip-pdf', ['payroll' => $this->payroll])->output();
    }
}
