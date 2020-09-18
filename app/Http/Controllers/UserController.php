<?php

namespace App\Http\Controllers;

use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller {
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index() {
        $users = User::all();
        return response()->json($users);
    }

    public function retrieve($id) {
        // error_log(auth()->user('id'));
        $user = User::find($id);
        if($user == null) {
            return response()->json(['message'=> 'Not found'], 404);
        }
        return response()->json($user);
    }

}