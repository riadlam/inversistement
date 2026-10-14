<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $fillable = [
        'code',
        'label_fr',
        'label_ar',
        'type',
    ];
}
