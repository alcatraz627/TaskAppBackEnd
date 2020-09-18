<?php

namespace App\Http\Controllers;

use App\Task;
use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class TaskController extends Controller {
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        $tasks = Task::all();
        return response()->json($tasks);
    }
}