<?php

namespace App\Http\Controllers;

use Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\VerifyUser;

class AuthController extends Controller {
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'register', 'verify']]);
    }

    private function generateToken($length) {
        return bin2hex(random_bytes($length));        
    }

    public function register(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        try {
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);

            $user->save();

            $verifToken = new VerifyUser;
            $verifToken->user_id = $user->id;
            $verifToken->token = $this->generateToken(20);

            $verifToken->save();

            return response()->json(['user'=>$user, 'message'=>'User created successfully. Verification token '.($verifToken->token).' sent to email'], 201);

        } catch (\Throwable $th) {
            error_log($th);
            return response()->json(['message' => "User registration failed"], 409);
        }
    }

    public function verify(String $token) {
        $verifEntry = VerifyUser::where('token', $token)->first();
        if($verifEntry) {
            $user = User::find($verifEntry->user_id);
            $user->verified = true;
            $user->save();

            $verifEntry->delete();

            return response()->json(['message'=>'User verified successfully. You can now login.'], 201);
        }
        return response()->json(['message'=>'Invalid or expired token.'], 400);
    }

    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if(!$token = Auth::attempt($credentials)) {
            return response()->json(['message'=> 'Unauthorized'], 401);
        }

        if(!Auth::user()->verified) {
            return response()->json(['message' => 'Please verify your email address'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me() {
        error_log(Auth::user()->verified);
        return(Auth::user());
    }

}