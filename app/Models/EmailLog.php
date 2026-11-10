<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One outgoing email, from the moment it is queued to its final state.
 *
 * The row is created before the queue push on purpose. If the worker is down,
 * or the job throws before it is ever picked up, the row is still there in
 * `queued` and the failure is visible instead of invisible.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $mailable
 * @property string $type
 * @property string $recipient_email
 * @property string|null $recipient_name
 * @property string $subject
 * @property string $status
 * @property int $attempts
 * @property string|null $error_message
 * @property array|null $context
 * @property \Illuminate\Support\Carbon|null $queued_at
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 */
class EmailLog extends Model
{
    /** Waiting for a worker, or in flight. */
    public const STATUS_QUEUED = 'queued';

    /** Handed to the mail transport without error. */
    public const STATUS_SENT = 'sent';

    /** The transport rejected it, or it exhausted its retries. */
    public const STATUS_FAILED = 'failed';

    /** Plain invoice delivery. */
    public const TYPE_INVOICE = 'invoice';

    /** Daily nudge for an invoice past its due date. */
    public const TYPE_OVERDUE_REMINDER = 'overdue_reminder';

    /** Payslip sent to an employee when payroll is marked paid. */
    public const TYPE_PAYSLIP = 'payslip';

    /** Leave approval notice sent to employee when HR approves a request. */
    public const TYPE_LEAVE_APPROVED = 'leave_approved';

    /** Meeting invitation sent to a lead. */
    public const TYPE_MEETING_INVITE = 'meeting_invite';

    /** Meeting reschedule notification sent to a lead. */
    public const TYPE_MEETING_UPDATE = 'meeting_update';

    /** Meeting cancellation notification sent to a lead. */
    public const TYPE_MEETING_CANCEL = 'meeting_cancel';

    /**
     * Dedicated queue, so a mail backlog can be inspected and worked on its
     * own: php artisan queue:work --queue=emails
     */
    public const QUEUE = 'emails';

    protected $fillable = [
        'user_id',
        'mailable',
        'type',
        'recipient_email',
        'recipient_name',
        'subject',
        'status',
        'attempts',
        'error_message',
        'context',
        'queued_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'context' => 'array',
        'attempts' => 'integer',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Open a log row for an email that is about to be queued.
     *
     * Kept as a named constructor rather than a raw create() so every caller
     * gets the same initial state, and so "the subject is captured at queue
     * time" is a property of the log rather than a habit of each service.
     *
     * @param  array<string, mixed>  $context
     */
    public static function queue(
        string $mailable,
        string $type,
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        array $context = [],
        ?int $userId = null,
    ): self {
        return static::create([
            'user_id' => $userId,
            'mailable' => $mailable,
            'type' => $type,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'status' => self::STATUS_QUEUED,
            'attempts' => 0,
            'context' => $context,
            'queued_at' => now(),
        ]);
    }

    /**
     * Terminal success. Clears any prior error so a row that failed once and
     * succeeded on retry reads as sent, not as both.
     */
    public function markSent(): self
    {
        $this->forceFill([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
            'failed_at' => null,
        ])->save();

        return $this;
    }

    public function markFailed(?\Throwable $exception): self
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $exception?->getMessage() ?? 'Unknown delivery failure.',
        ])->save();

        return $this;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
