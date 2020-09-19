<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyUser extends Model {

    protected $table = 'register_verif';
    protected $fillable = ['user_id', 'token'];
}

