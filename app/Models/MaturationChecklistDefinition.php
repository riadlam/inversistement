<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaturationChecklistDefinition extends Model
{
    protected $table = 'maturation_checklist_definitions';

    protected $fillable = [
        'key',
        'label',
        'sort_order',
    ];
}
