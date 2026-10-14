<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A persisted Score de Performance Unique snapshot (TDR §7.3), kept so the
 * dashboards can trend the score rather than only show today's value.
 */
class SpuScore extends Model
{
    protected $fillable = [
        'project_id',
        'score',
        'cp_score',
        'physical_score',
        'delays_score',
        'file_score',
        'traffic_light',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'cp_score' => 'integer',
            'physical_score' => 'integer',
            'delays_score' => 'integer',
            'file_score' => 'integer',
            'computed_at' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
