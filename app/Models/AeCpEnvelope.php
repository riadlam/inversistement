<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AE and CP notified to an operation for a given exercise (LOLF 18-15 art. 6).
 */
class AeCpEnvelope extends Model
{
    protected $fillable = [
        'project_id',
        'year',
        'ae_notified_da',
        'cp_notified_da',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'ae_notified_da' => 'decimal:2',
            'cp_notified_da' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
