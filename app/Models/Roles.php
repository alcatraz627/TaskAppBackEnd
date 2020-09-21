<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model {

    use SoftDeletes;

    protected $table = 'roles';
    protected $fillable = ['name', 'description', 'permissions'];
    protected $casts = [
        'permissions' => 'array',
    ];
}

