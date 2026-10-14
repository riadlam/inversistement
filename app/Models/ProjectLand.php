<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Assiette foncière. Recorded in the Algerian hectare / are / centiare form
 * ("11 h 94 ar 42 ca"); the m² total is derived by the calculation engine.
 */
class ProjectLand extends Model
{
    protected $table = 'project_land';

    protected $fillable = [
        'project_id',
        'affectation_decision_ref',
        'affectation_decision_date',
        'superficie_ha',
        'superficie_a',
        'superficie_ca',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'affectation_decision_date' => 'date',
            'superficie_ha' => 'integer',
            'superficie_a' => 'integer',
            'superficie_ca' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function label(): string
    {
        return sprintf('%d h %02d ar %02d ca', $this->superficie_ha, $this->superficie_a, $this->superficie_ca);
    }
}
