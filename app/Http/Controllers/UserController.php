<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $AuthController;

    public function __construct(AuthController $AuthController)
    {
        $this->middleware('auth');
        $this->AuthController = $AuthController;
    }

    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function retrieve($id)
    {
        // error_log(auth()->user('id'));
        $user = User::find($id);
        if ($user == null) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($user);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        try {
            [$user, $verifToken] = $this->AuthController->createUser($request->only('name', 'email', 'password'));

            return response()->json(['user' => $user->only('name', 'email', 'id', 'role'), 'message' => 'Normal user created successfully. Verification token ' . ($verifToken->token) . ' sent to user\'s email'], 201);
        } catch (\Throwable $th) {
            error_log($th);
            return response()->json(['message' => "User registration failed"], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'string',
            'email' => 'email|unique:users',
            'password' => 'string',
            // 'roles' =>
        ]);

        if ($id == Auth::user()->id) {
            $fields = ['name', 'email', 'password'];
            $user = Auth::user();

            foreach ($fields as $field) {
                if (!is_null($request->input($field))) $user[$field] = $request->input($field);
                if (!is_null($request->input('password'))) $user->password = app('hash')->make($request->input('password'));
            }

            $user->save();

            return response()->json($user->only('name', 'email', 'id', 'role'));
        }

        // TODO: Admin can change role. Do after ENUM for perms. Update: Do it in a different method
        return response();
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User with ID ' . $id . ' does not exist'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User with ID ' . $id . ' deleted'], 200);
    }
}
