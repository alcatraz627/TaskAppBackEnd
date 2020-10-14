<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

    public function index(Request $request)
    {
        // $users = User::all();
        $users = DB::table('users')->select(array_merge((new User())->getFillable(), ['id']));

        $search = $request->input('search');
        $isVerified = $request->input('isVerified');

        if ($isVerified) {
            if ($isVerified == "VERIFIED") {
                $users = $users->where('verified', '=', 1);
            } else if ($isVerified == "NOT_VERIFIED") {
                $users = $users->where('verified', '=', 0);
            }
        }

        error_log($search);
        if (!!$search) {
            $users = $users
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        }

        return response()->json($this->paginate($users->get(), $request));
    }

    public function retrieve($id)
    {
        // error_log(auth()->user('id'));
        $user_id = ($id == "me") ? Auth::user()->id : $id;
        $user = User::find($user_id);
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
            'password' => 'required|confirmed|min:6'
        ]);

        try {
            [$user, $verifToken] = $this->AuthController->createUser($request->only('name', 'email', 'password'), Auth::user()->id);

            return response()->json(['user' => $user, 'message' => 'User ' . $user['name'] . ' created successfully!.', 'type' => 'SUCCESS'], 201);
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

        try {
            if ($id == Auth::user()->id) {
                $fields = ['name', 'email', 'password'];
                $user = Auth::user();

                foreach ($fields as $field) {
                    if (!is_null($request->input($field))) $user[$field] = $request->input($field);
                    if (!is_null($request->input('password'))) $user->password = app('hash')->make($request->input('password'));
                }

                $user->save();

                return response()->json(['user' => $user, 'message' => 'User updated successfully!', 'type' => 'SUCCESS']);
            }
        } catch (\Throwable $th) {
            error_log($th);
            return response()->json(['message' => "User registration failed"], 500);
        }
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

    public function tasklist(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $tasks = DB::table('tasks')->where('created_by', '=', Auth::user()->id)
            ->orWhere('assigned_to', '=', Auth::user()->id);

        // $tasks = Task::where('assigned_to', $id)->orWhere('created_by', $id)->get()->toArray();

        $search = $request->input('search');
        $taskStatus = $request->input('taskStatus');

        if (!!$search) {
            $tasks = $tasks
                ->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'like', '%' . $search . '%');
        }

        if (!!$taskStatus) {
            $tasks = $tasks->where('status', '=', $taskStatus);
        }

        // $tasks = $tasks->get()->toArray();

        // $getId = function ($task) {
        //     return $task['id'];
        // };

        // $filterByParam = function ($param) use ($id) {
        //     return function ($task) use ($id, $param) {
        //         return $task[$param] == $id;
        //     };
        // };

        // $assigned_to = array_values(array_map($getId, array_filter($tasks, $filterByParam('assigned_to'))));
        // $created_by = array_values(array_map($getId, array_filter($tasks, $filterByParam('created_by'))));

        return response()->json($this->paginate($tasks->get(), $request));
    }
}
