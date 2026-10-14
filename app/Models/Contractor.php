<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An entreprise holding a marché. The scans name a different company per lot,
 * so the titulaire belongs to the lot rather than to the project.
 */
class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_form',
        'nif',
        'business_register',
        'address',
        'phone',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(ProjectLot::class);
    }

    /** "E.T.B MEZIANI AMAR" style display used across the fiche. */
    public function displayName(): string
    {
        return trim($this->name);
    }
}
