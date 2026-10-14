<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SousSecteur extends Model
{
    protected $table = 'sous_secteurs';

    protected $fillable = ['secteur_id', 'code', 'label_fr', 'label_ar'];

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class);
    }
}
