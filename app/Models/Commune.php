<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $table = 'commune';

    public $timestamps = false;

    protected $fillable = [
        'code_comm',
        'lib_comm_ar',
        'lib_comm_fr',
        'code_wilaya',
    ];
}
