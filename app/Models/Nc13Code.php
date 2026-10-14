<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nc13Code extends Model
{
    protected $fillable = [
        'code',
        'label',
        'category',
        'class_label',
        'budget_type',
    ];
}
