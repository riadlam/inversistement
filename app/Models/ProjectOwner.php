<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectOwner extends Model
{
    protected $fillable = [
        'email',
        'name',
        'company_name',
        'nif',
        'business_register',
    ];
}
