<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpProjection extends Model
{
    protected $fillable = [
        'project_id',
        'year_offset',
        'amount_da',
    ];

    protected function casts(): array
    {
        return [
            'amount_da' => 'decimal:2',
            'year_offset' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
