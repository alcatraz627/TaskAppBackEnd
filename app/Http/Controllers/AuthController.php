<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\VerifyUser;
use App\Models\Roles;
use App\Models\ForgotPass;

class AuthController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth', ['except' => ['login', 'register', 'verify']]);
    }

    private function generateToken($length)
    {
        return bin2hex(random_bytes($length));
    }

    public function createUser($data)
    {
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = app('hash')->make($data['password']);

        $admin = Roles::where('name', 'user')->first();
        $user->role = $admin->id;

        $user->save();

        $verifToken = new VerifyUser;
        $verifToken->user_id = $user->id;
        $verifToken->token = $this->generateToken(20);

        $verifToken->save();

        return ([$user, $verifToken]);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        try {
            [$user, $verifToken] = $this->createUser($request->only('name', 'email', 'password', 'password_confirmation'));

            return response()->json(['user' => $user->only('name', 'email', 'id', 'role'), 'message' => 'User created successfully. Verification token ' . ($verifToken->token) . ' sent to email'], 201);
        } catch (\Throwable $th) {
            error_log($th);
            return response()->json(['message' => "User registration failed"], 500);
        }
    }

    public function email_verify(String $token)
    {
        $verifEntry = VerifyUser::where('token', $token)->first();
        if ($verifEntry) {
            $user = User::find($verifEntry->user_id);
            $user->verified = true;
            $user->save();

            $verifEntry->delete();

            return response()->json(['message' => 'User verified successfully. You can now login.'], 201);
        }
        return response()->json(['message' => 'Invalid or expired token.'], 400);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!Auth::user()->verified) {
            return response()->json(['message' => 'Please verify your email address'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        error_log(json_encode(Roles::first()->permissions));
        return (Auth::user());
    }

    public function forgotpass_request(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'User with email ' . $request->input('email') . ' does not exist.'], 404);
        }

        if (!$user->verified) {
            return response()->json(['message' => 'Kindly complete email verification of ' . $request->input('email') . ' before resetting password'], 401);
        }

        $forgotPass = ForgotPass::firstOrNew(['user_id' => $user->id]);
        $forgotPass->token = $this->generateToken(10);
        $forgotPass->save();

        return response()->json(['token' => $forgotPass->token, 'message' => 'A password reset link has been sent to ' . $request->input('email') . '. Please continue from that link'], 201);
    }

    public function forgotpass_verify(Request $request)
    {
        $this->validate($request, [
            'token' => 'string',
        ]);

        $forgotPass = ForgotPass::where('token', $request->input('token'))->first();

        if (!$forgotPass) {
            return response()->json(['message' => 'Invalid password reset token'], 400);
        }

        return response()->json(['email' => $forgotPass->user()->email], 200);
    }

    public function forgotpass_reset(Request $request)
    {
        $this->validate($request, [
            'token' => 'string',
            'password' => 'required|confirmed',
        ]);
        try {
            $forgotPass = ForgotPass::where('token', $request->input('token'))->first();
            if (!$forgotPass) {
                return response()->json(['message' => 'Invalid password reset token'], 400);
            }
            $user = $forgotPass->user();
            $user->password = app('hash')->make($request->input('password'));
            $user->save();

            $forgotPass->delete();

            return response()->json(['message' => 'Password updated successfully. You can now login.'], 200);

        } catch (\Throwable $th) {
            error_log($th);
            return response()->json(['message' => 'An error occured. Please try again later'], 500);
        }
    }
}
