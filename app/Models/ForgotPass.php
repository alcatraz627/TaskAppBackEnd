<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForgotPass extends Model {

    protected $table = 'forgot_pass';
    protected $fillable = ['user_id', 'token'];

    public function user() {
        return User::find($this->user_id);
    }
}

