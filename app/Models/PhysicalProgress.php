<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalProgress extends Model
{
    use HasFactory;

    protected $table = 'physical_progress_entries';

    protected $fillable = [
        'project_id',
        'title',
        'progress_pct',
        'reported_by',
        'status',
        'accepted_by',
        'declined_by',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_pct' => 'decimal:1',
            'reported_at' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function declinedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declined_by');
    }
}
