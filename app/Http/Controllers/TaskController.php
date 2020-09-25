<?php

namespace App\Http\Controllers;

use App\Models\Task;
// use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        // error_log(auth()->user('id'));
        $tasks = Task::all();
        return response()->json($tasks);
    }

    public function retrieve($id)
    {
        // error_log(auth()->user('id'));
        $task = Task::find($id);
        if ($task == null) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($task);
    }

    public function create(Request $request)
    {
        $TASK_STATUS = config('enums.task_status');

        $this->validate($request, [
            'title' => 'required',
            'description' => 'string',
            'status' => 'in:' . join(",", array_keys($TASK_STATUS)),
            // 'created_by' => 'required|exists:users,id', 
            'assigned_to' => 'exists:users,id',
            'due_date' => 'date_format:Y-m-d H:i:s' //'Y-m-d H:i:s'
        ]);

        $data = $request->only(['title', 'description', 'status', 'assigned_to', 'due_date']);

        $data['created_by'] = Auth::user()->id;
        $data['status'] = $TASK_STATUS[array_key_exists('status', $data)  ? $data['status'] : array_keys($TASK_STATUS)[0]];

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        error_log(json_encode($request->all()));
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $this->validate($request, [
            'title' => 'min:1',
            'description' => 'nullable',
            'assigned_to' => 'nullable|sometimes|exists:users,id',
            'due_date' => 'date_format:Y-m-d H:i:s|nullable', //'Y-m-d H:i:s'

            'status' => 'in:' . join(",", array_keys(config('enums.task_status'))),
        ]);

        if ($task->created_by == Auth::user()->id) {
            $data = $request->only(['title', 'description', 'assigned_to', 'due_date']);
        } else if ($task->assigned_to == Auth::user()->id) {
            $data = $request->only(['status']);
        } else {
            return response()->json(['message' => 'Unauthorized. Only Creators or Assignees can update tasks', 403]);
        }

        $task->update($data);
        return response()->json($task, 201);
    }
}
