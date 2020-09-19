<?php

namespace App\Http\Controllers;

use App\Models\Task;
// use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class TaskController extends Controller {
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index() {
        // error_log(auth()->user('id'));
        $tasks = Task::all();
        return response()->json($tasks);
    }

    public function retrieve($id) {
        // error_log(auth()->user('id'));
        $task = Task::find($id);
        if($task == null) {
            return response()->json(['message'=> 'Not found'], 404);
        }
        return response()->json($task);
    }
}