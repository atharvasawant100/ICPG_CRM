<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\EmailLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Delivers one email and keeps its log row honest.
 *
 * Only the log id travels through the queue. Re-reading the record at send
 * time means a retry picks up edits made since the job was pushed, rather
 * than mailing a stale copy from the serialized payload.
 *
 * Subclasses say what to build (buildMailable) and what to count afterwards
 * (afterSent). Everything about retrying and status transitions lives here so
 * those rules cannot differ between one email type and the next.
 */
abstract class SendQueuedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Transient SMTP hiccups are worth another go; a wrong address is not, so
     * three attempts is enough to clear a restart without re-mailing for hours.
     */
    public int $tries = 3;

    /** Space the attempts out so a host outage is not hammered. */
    public array $backoff = [30, 120];

    public function __construct(public int $emailLogId)
    {
    }

    /**
     * The message to send.
     *
     * Throw when the underlying record has gone: a missing row will never
     * appear on a retry, so it must not burn the attempt budget.
     */
    abstract protected function buildMailable(EmailLog $log): Mailable;

    /**
     * Run once the transport accepted the message: bump whatever counters the
     * record itself keeps. Must never throw, or a delivered email is re-sent.
     */
    protected function afterSent(EmailLog $log): void
    {
    }

    public function handle(EmailLogService $service): void
    {
        $log = EmailLog::find($this->emailLogId);

        // The log row can disappear if the trail was purged between dispatch and
        // delivery. Nothing to send, and nothing to record.
        if ($log === null) {
            return;
        }

        /*
         | A retry after a partial delivery must not mail the recipient twice.
         | Marking the log sent happens immediately after the transport
         | returns, so this is the one place a duplicate can be prevented.
         */
        if ($log->status === EmailLog::STATUS_SENT) {
            return;
        }

        $log->forceFill([
            'attempts' => (int) $log->attempts + 1,
        ])->save();

        // Built exactly once: an invoice payslip renders a PDF, and doing that
        // twice per attempt is pure waste.
        $service->deliver($this->buildMailable($log));

        $log->markSent();
        $this->afterSent($log);
    }

    /**
     * Called once the retries are exhausted.
     *
     * Leaving the row in `queued` after a permanent failure would understate
     * the problem, so the terminal state is written here rather than in
     * handle(), where the exception is still in flight.
     */
    public function failed(?Throwable $exception): void
    {
        $log = EmailLog::find($this->emailLogId);

        if ($log === null) {
            return;
        }

        $log->markFailed($exception);

        Log::error('Email permanently failed', [
            'email_log_id' => $log->id,
            'type' => $log->type,
            'recipient_email' => $log->recipient_email,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Load the record an email log points at.
     *
     * @template T of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<T>  $model
     * @param  string  $key  the context key holding the id
     * @return T
     *
     * @throws RuntimeException when the id is missing or the row is gone
     */
    protected function resolveRecord(EmailLog $log, string $model, string $key): object
    {
        $id = $log->context[$key] ?? null;

        $record = $id ? $model::find($id) : null;

        if ($record === null) {
            throw new RuntimeException(
                sprintf('%s #%s for email log %d no longer exists.', class_basename($model), $id ?? '?', $log->id)
            );
        }

        return $record;
    }
}
