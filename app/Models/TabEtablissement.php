<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabEtablissement extends Model
{
    protected $table = 'tab_etablissement';

    public $timestamps = false;

    protected $fillable = [
        'wilaya',
        'Code établissement',
        'libelle',
    ];

    public function getCodeAttribute(): string
    {
        return $this->{'Code établissement'};
    }
}
