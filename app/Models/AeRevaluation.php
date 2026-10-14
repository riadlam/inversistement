<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A réévaluation of an operation's autorisation de programme.
 *
 * The scanned fiche shows these numbered per project ("1ère réévaluation" to
 * "5ème réévaluation"), each with its own decision reference and year, which is
 * what `sequence`, `year` and `decision_ref` carry.
 */
class AeRevaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'sequence',
        'year',
        'decision_ref',
        'requested_amount_da',
        'justification',
        'status',
        'submitted_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'year' => 'integer',
            'requested_amount_da' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
