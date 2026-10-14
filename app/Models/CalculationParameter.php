<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationParameter extends Model
{
    protected $fillable = [
        'key',
        'label_fr',
        'category',
        'value',
        'type',
        'min',
        'max',
        'unit',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'min' => 'float',
            'max' => 'float',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Cast to the declared type so `dossier_complet ? x : 0` behaves. */
    public function typedValue(): float|int|bool
    {
        return match ($this->type) {
            'int' => (int) $this->value,
            'bool' => (bool) $this->value,
            default => (float) $this->value,
        };
    }
}
