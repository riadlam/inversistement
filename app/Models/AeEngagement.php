<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A legal commitment of authorised expenditure (engagement d'AE).
 */
class AeEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'amount_da',
        'description',
        'engaged_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_da' => 'decimal:2',
            'engaged_at' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
