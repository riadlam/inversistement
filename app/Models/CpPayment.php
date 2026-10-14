<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'amount_da',
        'payment_type',
        'description',
        'paid_at',
        'created_by',
        'invoice',
        'invoice_by_ministry',
        'status',
        'rejection_cause',
        'mandate_number',
    ];

    protected function casts(): array
    {
        return [
            'amount_da' => 'decimal:2',
            'paid_at' => 'date',
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
