<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** P1 to P4. */
class ProgrammeStructure extends Model
{
    protected $fillable = ['code', 'label_fr'];

    public function sousProgrammes(): HasMany
    {
        return $this->hasMany(SousProgramme::class);
    }
}
