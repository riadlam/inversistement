<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** PACE, PCCE, PN, FSDRS. */
class ProgrammeType extends Model
{
    protected $fillable = ['code', 'label_fr'];
}
