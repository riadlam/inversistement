<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SousProgramme extends Model
{
    protected $table = 'sous_programmes';

    protected $fillable = ['programme_structure_id', 'code', 'label_fr'];

    public function structure(): BelongsTo
    {
        return $this->belongsTo(ProgrammeStructure::class, 'programme_structure_id');
    }
}
