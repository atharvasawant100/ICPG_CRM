<?php

namespace App\Rbac;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Writes to audit_logs.
 *
 * Every write is wrapped in try/catch and reported to the Laravel log: an audit
 * failure must never break the request that triggered it.
 */
class AuditLogger
{
    /**
     * Attributes that must never be persisted to the audit trail.
     *
     * @var list<string>
     */
    public const SECRET_ATTRIBUTES = [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'token',
        'secret',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Redact secrets recursively from an attribute bag.
     */
    public static function redact(array $attributes): array
    {
        $clean = [];

        foreach ($attributes as $key => $value) {
            if (in_array(strtolower((string) $key), self::SECRET_ATTRIBUTES, true)) {
                $clean[$key] = '[redacted]';

                continue;
            }

            $clean[$key] = is_array($value) ? self::redact($value) : $value;
        }

        return $clean;
    }

    /**
     * Record an audit entry. Never throws.
     */
    public static function record(
        string $action,
        string $module,
        int|string|null $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): void {
        try {
            AuditLog::create([
                'user_id' => $userId ?? (auth()->id() ?: null),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'old_values' => $oldValues === null ? null : self::redact($oldValues),
                'new_values' => $newValues === null ? null : self::redact($newValues),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) (request()->userAgent() ?? ''), 0, 1000) ?: null,
            ]);
        } catch (\Throwable $e) {
            // Deliberately swallowed: auditing must not break the request.
            Log::error('Audit log write failed.', [
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Record a model event, capturing the changed attributes.
     */
    public static function recordModelEvent(string $action, string $module, Model $model): void
    {
        // Union the global secret list with anything the model declares.
        $excluded = array_map('strtolower', array_merge(
            self::SECRET_ATTRIBUTES,
            method_exists($model, 'auditExcluded') ? $model->auditExcluded() : [],
        ));

        $old = $action === 'update' ? self::omit($model->getOriginal(), $excluded) : null;
        $new = $action === 'delete' ? null : self::omit($model->getAttributes(), $excluded);

        self::record($action, $module, $model->getKey(), $old, $new);
    }

    /**
     * Strip excluded keys rather than redacting them, so a secret is not even
     * present in the payload handed to the redactor.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $excluded
     * @return array<string, mixed>
     */
    private static function omit(array $attributes, array $excluded): array
    {
        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Record an auth event (login / logout / failed login).
     */
    public static function recordAuth(string $action, ?int $userId, array $context = []): void
    {
        self::record($action, 'auth', $userId, null, $context ?: null);
    }
}