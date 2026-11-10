<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendInvoiceEmail;
use App\Mail\InvoiceMail;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceEmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Rbac\RbacTestCase;

/**
 * Automatic invoice email delivery.
 *
 * The rule under test: sending or approving an invoice always queues an email,
 * unless the caller explicitly opted out with send_email=0. Delivery happens
 * on the queue, and every attempt leaves a row in email_logs.
 */
class InvoiceEmailAutoSendTest extends RbacTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRbac();
    }

    public function test_sending_an_invoice_queues_an_email(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'draft');

        $this->actingAs($admin)
            ->post(route('invoices.send', $invoice), ['send_email' => 1])
            ->assertRedirect();

        $this->assertSame('sent', $invoice->fresh()->status);

        Queue::assertPushed(SendInvoiceEmail::class, function (SendInvoiceEmail $job) {
            return $job->emailLogId === EmailLog::latest('id')->value('id');
        });

        $log = EmailLog::latest('id')->first();
        $this->assertSame(EmailLog::STATUS_QUEUED, $log->status);
        $this->assertSame(InvoiceMail::TYPE_INVOICE, $log->type);
        $this->assertSame('customer@example.com', $log->recipient_email);
        $this->assertSame($invoice->id, $log->context['invoice_id']);
        $this->assertSame($admin->id, $log->user_id);
    }

    public function test_sending_defaults_to_emailing_even_without_the_flag(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'draft');

        // No send_email key at all: the default has to be "send it".
        $this->actingAs($admin)->post(route('invoices.send', $invoice));

        Queue::assertPushed(SendInvoiceEmail::class);
        $this->assertSame(1, EmailLog::count());
    }

    public function test_explicit_opt_out_marks_sent_without_queuing(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'draft');

        $this->actingAs($admin)
            ->post(route('invoices.send', $invoice), ['send_email' => 0])
            ->assertSessionHas('success');

        $this->assertSame('sent', $invoice->fresh()->status);
        Queue::assertNothingPushed();
        $this->assertSame(0, EmailLog::count());
    }

    public function test_approving_a_draft_via_edit_queues_an_email(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'draft');

        $this->actingAs($admin)->put(route('invoices.update', $invoice), $this->invoicePayload(status: 'sent'))
            ->assertRedirect(route('invoices.show', $invoice));

        Queue::assertPushed(SendInvoiceEmail::class);
        $this->assertSame(1, EmailLog::count());
    }

    public function test_editing_a_sent_invoice_does_not_send_again(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent');

        $this->actingAs($admin)->put(route('invoices.update', $invoice), $this->invoicePayload(status: 'sent'));

        // The customer already has this invoice; a notes edit is not a resend.
        Queue::assertNothingPushed();
        $this->assertSame(0, EmailLog::count());
    }

    public function test_a_customer_without_an_email_is_reported_not_thrown(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $customer = Customer::create([
            'name' => 'No Address Ltd',
            // The column is NOT NULL, so "no address" is an empty string here
            // rather than a missing key.
            'email' => '',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-NOEMAIL',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 100,
            'total_amount' => 100,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('invoices.send', $invoice), ['send_email' => 1])
            ->assertSessionHas('error');

        // The invoice is still marked sent; only the email was skipped.
        $this->assertSame('sent', $invoice->fresh()->status);
        Queue::assertNothingPushed();
    }

    public function test_the_job_marks_the_log_sent_and_counts_the_delivery(): void
    {
        // Queue::fake stops dispatch() from running the job inline, which the
        // sync test connection would otherwise do, making handle() untestable.
        Queue::fake();
        Mail::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent');

        $log = (new InvoiceEmailService)->queueInvoice($invoice, false, $admin->id);
        $this->assertSame(EmailLog::STATUS_QUEUED, $log->status);

        (new SendInvoiceEmail($log->id))->handle(app(InvoiceEmailService::class));

        $this->assertSame(EmailLog::STATUS_SENT, $log->fresh()->status);
        $this->assertNotNull($log->fresh()->sent_at);
        $this->assertSame(1, (int) $invoice->fresh()->email_sent_count);

        Mail::assertSent(InvoiceMail::class);
    }

    public function test_the_job_is_idempotent_so_a_retry_cannot_double_send(): void
    {
        Queue::fake();
        Mail::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent');

        $log = (new InvoiceEmailService)->queueInvoice($invoice, false, $admin->id);
        $job = new SendInvoiceEmail($log->id);

        $job->handle(app(InvoiceEmailService::class));
        $job->handle(app(InvoiceEmailService::class));

        Mail::assertSentCount(1);
        $this->assertSame(1, (int) $invoice->fresh()->email_sent_count);
    }

    public function test_a_failed_send_leaves_the_log_queued_for_the_retry(): void
    {
        Queue::fake();
        Mail::shouldReceive('mailer->send')->andThrow(new \RuntimeException('SMTP refused the connection'));

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent');

        $log = (new InvoiceEmailService)->queueInvoice($invoice, false, $admin->id);

        try {
            (new SendInvoiceEmail($log->id))->handle(app(InvoiceEmailService::class));
            $this->fail('The transport error should have propagated.');
        } catch (\RuntimeException $e) {
            $this->assertSame('SMTP refused the connection', $e->getMessage());
        }

        // Still queued: the job will be retried, so it must not look finished.
        $this->assertSame(EmailLog::STATUS_QUEUED, $log->fresh()->status);
        $this->assertSame(0, (int) $invoice->fresh()->email_sent_count);
    }

    public function test_exhausted_retries_write_the_failure_onto_the_log(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent');

        $log = (new InvoiceEmailService)->queueInvoice($invoice, false, $admin->id);

        (new SendInvoiceEmail($log->id))->failed(new \RuntimeException('SMTP refused the connection'));

        $log->refresh();

        $this->assertSame(EmailLog::STATUS_FAILED, $log->status);
        $this->assertSame('SMTP refused the connection', $log->error_message);
        $this->assertNotNull($log->failed_at);
        $this->assertSame(0, (int) $invoice->fresh()->email_sent_count);
    }

    public function test_overdue_reminder_counters_are_kept_off_the_invoice_email_fields(): void
    {
        Queue::fake();
        Mail::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent', dueDate: now()->subDay());

        $log = (new InvoiceEmailService)->queueOverdueReminder($invoice, false, $admin->id);
        (new SendInvoiceEmail($log->id))->handle(app(InvoiceEmailService::class));

        $invoice->refresh();

        $this->assertSame(1, (int) $invoice->overdue_reminder_sent_count);
        $this->assertNotNull($invoice->overdue_reminder_last_sent_at);
        $this->assertSame(0, (int) $invoice->email_sent_count);
    }

    public function test_overdue_reminders_are_queued_once_per_invoice_per_day(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $invoice = $this->invoice($admin, status: 'sent', dueDate: now()->subDay());

        $this->artisan('invoices:send-overdue-reminders')->assertSuccessful();
        $this->artisan('invoices:send-overdue-reminders')->assertSuccessful();

        $this->assertSame(1, EmailLog::count(), 'A second run on the same day must not re-mail the customer.');
        $this->assertSame(InvoiceMail::TYPE_OVERDUE_REMINDER, EmailLog::first()->type);
    }

    // ---- helpers ----------------------------------------------------------

    private function invoice(User $admin, string $status = 'draft', ?string $dueDate = null): Invoice
    {
        $customer = Customer::create([
            'name' => 'Acme ' . uniqid(),
            'email' => 'customer@example.com',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        return Invoice::create([
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => $dueDate ?? now()->addDays(15)->toDateString(),
            'subtotal' => 1000,
            'total_amount' => 1000,
            'status' => $status,
            'created_by' => $admin->id,
        ]);
    }

    private function invoicePayload(string $status): array
    {
        return [
            'customer_id' => 1,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => $status,
            'items' => [
                ['description' => 'Work', 'quantity' => 1, 'unit_price' => 1000],
            ],
        ];
    }
}
