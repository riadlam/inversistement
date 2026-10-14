<?php

namespace App\Observers;

use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an audit row on every sensitive create/update/delete. Only dirty
 * attributes are snapshotted so the journal stays readable.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        AuditLogger::record(
            __('ui.audit.created', ['model' => class_basename($model)]),
            $model,
            after: $this->snapshot($model),
        );
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getChanges();
        unset($dirty['updated_at']);

        if ($dirty === []) {
            return;
        }

        $before = [];
        foreach (array_keys($dirty) as $key) {
            $before[$key] = $model->getOriginal($key);
        }

        AuditLogger::record(
            __('ui.audit.updated', ['model' => class_basename($model)]),
            $model,
            before: $before,
            after: $dirty,
        );
    }

    public function deleted(Model $model): void
    {
        AuditLogger::record(
            __('ui.audit.deleted', ['model' => class_basename($model)]),
            $model,
            before: $this->snapshot($model),
        );
    }

    /** @return array<string, mixed> */
    private function snapshot(Model $model): array
    {
        $hidden = array_flip(['password', 'remember_token']);

        return collect($model->attributesToArray())
            ->reject(fn ($_, $key) => isset($hidden[$key]))
            ->all();
    }
}
