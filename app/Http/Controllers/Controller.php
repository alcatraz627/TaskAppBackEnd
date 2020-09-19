<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Roles;

use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected function respondWithToken($token) {
        return response()->json([
            'token'=>$token,
            'token_type'=>'Token',
            'expires_in'=> Auth::factory()->getTTL() * 60
        ], 200);
    }

    protected function hasPermission(User $user, String $permission) {
        return in_array($permission, Roles::find($user->role)->permissions);

    }
}
