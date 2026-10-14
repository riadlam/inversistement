<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A maîtrise d'œuvre mission and the approval dates of its three stages. */
class ProjectDesignMission extends Model
{
    protected $fillable = [
        'project_id',
        'design_office_id',
        'bet_name',
        'esquisse_approved_at',
        'avant_projet_approved_at',
        'projet_execution_approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'esquisse_approved_at' => 'date',
            'avant_projet_approved_at' => 'date',
            'projet_execution_approved_at' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function designOffice(): BelongsTo
    {
        return $this->belongsTo(DesignOffice::class);
    }
}
