<?php

namespace App\Http\Controllers;

use App\Models\Task;
// use App\Models\User;
use App\Models\Roles;

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
        // When reading user->role, it returns the name of the role in the roles db instead of the ID
        if (Auth::user()->role == config('enums.roles')['ADMIN']) {
            $tasks = Task::all();
        } else {
            $tasks = Task::where('created_by', '=', Auth::user()->id)
                ->orWhere('assigned_to', '=', Auth::user()->id)->get();
        }
        return response()->json($tasks);
    }

    public function retrieve($id)
    {
        $task = Task::find($id);
        if ($task == null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $admin = Roles::where('name', config('enums.roles')['ADMIN'])->first();
        if (Auth::user()->role == $admin->role || in_array(Auth::user()->id, [$task->created_by, $task->assigned_to])) {

            return response()->json($task);
        } else {
            return response()->json(['message' => 'Unauthorized. Only Creators or Assignees can view', 401]);
        }
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
        $TASK_STATUS = config('enums.task_status');
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $this->validate($request, [
            'title' => 'min:1',
            'description' => 'nullable',
            'assigned_to' => 'nullable|sometimes|exists:users,id',
            'due_date' => 'date_format:Y-m-d H:i:s|nullable', //'Y-m-d H:i:s'

            'status' => 'in:' . join(",", array_keys($TASK_STATUS)),
        ]);

        if (!in_array(Auth::user()->id, [$task->assigned_to, $task->created_by])) {
            return response()->json(['message' => 'Unauthorized. Only Creators or Assignees can update tasks', 401]);
        }

        if ($task->created_by == Auth::user()->id) {
            $data = $request->only(['title', 'description', 'assigned_to', 'due_date']);
        }

        if ($task->assigned_to == Auth::user()->id && $request->has('status')) {
            error_log($TASK_STATUS[$request->input('status')]);
            $data['status'] = $TASK_STATUS[$request->input('status')];
        }

        $task->update($data);
        return response()->json(['task' => $task, 'message' => 'Task updated successfully'], 201);
    }

    public function delete($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if (Auth::user()->id == $task->id) {
            $task->delete();
            return response()->json(['message' => 'Task deleted'], 204);
        } else {
            return response()->json(['message' => 'You are not authorized to delete this task'], 401);;
        }
    }
}
