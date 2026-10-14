<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationRule extends Model
{
    protected $fillable = [
        'key',
        'label_fr',
        'label_ar',
        'category',
        'expression',
        'variables',
        'unit',
        'precision',
        'clamp_min',
        'clamp_max',
        'is_active',
        'version',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'precision' => 'integer',
            'clamp_min' => 'float',
            'clamp_max' => 'float',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
