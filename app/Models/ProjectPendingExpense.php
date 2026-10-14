<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A line of the "ETAT DES DEPENSES EN INSTANCE D'ENGAGEMENT" table. */
class ProjectPendingExpense extends Model
{
    protected $fillable = [
        'project_id',
        'lot_number',
        'designation',
        'amount_da',
        'kind',
    ];

    protected function casts(): array
    {
        return [
            'lot_number' => 'integer',
            'amount_da' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public static function kindLabels(): array
    {
        return [
            'avenant' => 'Avenant',
            'travaux_supplementaires' => 'Travaux supplémentaires',
            'complementaires' => 'Travaux complémentaires',
            'autre' => 'Autre',
        ];
    }
}
