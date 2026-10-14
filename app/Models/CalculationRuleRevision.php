<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationRuleRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'target_type',
        'target_key',
        'old_value',
        'new_value',
        'version',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
