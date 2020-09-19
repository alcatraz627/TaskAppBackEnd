<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model {

    protected $table = 'roles';
    protected $fillable = ['name', 'description', 'permissions'];
    protected $casts = [
        'permissions' => 'array',
    ];
}

