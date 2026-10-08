<?php

namespace App\Rbac\Concerns;

use App\Rbac\AuditLogger;

/**
 * Model-level audit trail on created / updated / deleted.
 *
 * Declare the audit "module" name via $auditModule, or override auditModule().
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLogger::recordModelEvent('create', $model->auditModule(), $model);
        });

        static::updated(function ($model) {
            // Ignore no-op saves.
            if (! $model->wasChanged()) {
                return;
            }

            AuditLogger::recordModelEvent('update', $model->auditModule(), $model);
        });

        static::deleted(function ($model) {
            AuditLogger::recordModelEvent('delete', $model->auditModule(), $model);
        });
    }

    /**
     * Model-specific extra secrets to redact, beyond the global list.
     *
     * @return list<string>
     */
    public function auditExcluded(): array
    {
        return property_exists($this, 'auditExclude') ? $this->auditExclude : [];
    }

    /**
     * The module label stored in audit_logs.module.
     */
    public function auditModule(): string
    {
        return $this->auditModule ?? class_basename($this);
    }
}