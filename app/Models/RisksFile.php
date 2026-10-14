<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RisksFile extends Model
{
    protected $fillable = [
        'project_id',
        'notes',
        'file',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
