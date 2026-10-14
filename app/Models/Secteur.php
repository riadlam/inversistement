<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secteur extends Model
{
    protected $fillable = ['code', 'label_fr', 'label_ar'];

    public function sousSecteurs(): HasMany
    {
        return $this->hasMany(SousSecteur::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
