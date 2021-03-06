<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForgotPass extends Model {

    use SoftDeletes;

    protected $table = 'forgot_pass';
    protected $fillable = ['user_id', 'token'];

    public function user() {
        return User::find($this->user_id);
    }
}

