<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fiche de clôture (TDR Fiche 06): the final AE / CP position recorded when an
 * operation is closed and locked to read-only.
 */
class ProjectClosure extends Model
{
    protected $fillable = [
        'project_id',
        'final_ae_engaged_da',
        'final_cp_consumed_da',
        'final_rar_da',
        'closure_decision_ref',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'final_ae_engaged_da' => 'decimal:2',
            'final_cp_consumed_da' => 'decimal:2',
            'final_rar_da' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
