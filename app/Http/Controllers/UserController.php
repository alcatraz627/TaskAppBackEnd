<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;

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
        $user_id = ($id == "me") ? Auth::user()->id : $id;
        $user = User::find($user_id);
        if ($user == null) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($user->only('name', 'email', 'id', 'role'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        try {
            [$user, $verifToken] = $this->AuthController->createUser($request->only('name', 'email', 'password'), Auth::user()->id);

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
        Task::where('assigned_to', $id)->update(['assigned_to' => null]);

        $user->deleted_by = Auth::user()->id;
        $user->save();
        // $user->update(['deleted_by' => Auth::user()->id]);
        $user->delete();
        return response()->json(['message' => 'User with ID ' . $id . ' deleted'], 200);
    }

    public function tasklist($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $tasks = Task::where('assigned_to', $id)->orWhere('created_by', $id)->get()->toArray();

        $getId = function ($task) {
            return $task['id'];
        };

        $filterByParam = function ($param) use ($id) {
            return function ($task) use ($id, $param) {
                return $task[$param] == $id;
            };
        };

        $assigned_to = array_values(array_map($getId, array_filter($tasks, $filterByParam('assigned_to'))));
        $created_by = array_values(array_map($getId, array_filter($tasks, $filterByParam('created_by'))));

        return response()->json(['tasks' => $tasks, 'created_by' => $created_by, 'assigned_to' => $assigned_to]);
    }
}
