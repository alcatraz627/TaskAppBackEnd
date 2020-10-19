<?php

namespace App\Http\Controllers;

use App\Jobs\MailJob;
use App\Mail\TaskAssignedMail;
use App\Mail\TaskStatusMail;

use Illuminate\Support\Facades\DB;
use App\Models\Task;
// use App\Models\User;
use App\Models\Roles;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
// use Illuminate\Support\Carbon;
// use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function serializeUser($task)
    {
        return [
            'task' => $task,
            'users' => [
                'assigned_to' => User::find($task->assigned_to),
                'created_by' => User::find($task->created_by)
            ]
        ];
    }

    public function serializeUsers($tasks)
    {
        return array_map(function ($task) {
            // error_log($task->id);
            return [
                'task' => $task,
                'users' => [
                    'assigned_to' => User::find($task->assigned_to),
                    'created_by' => User::find($task->created_by)
                ]
            ];
        }, $tasks->toArray());
        // return $tasks;
    }

    public function list(Request $request)
    {
        // $tasks = DB::table('tasks');
        $tasks = DB::table('tasks')->select(array_merge((new Task())->getFillable(), ['id']));

        if ($request->user()->hasPermission('task-list')) {
            // Do nothing because already fetched
        } else {
            $tasks->where('created_by', $request->user())->orWhere('assigned_to', $request->user()->id);
        }

        $search = $request->input('search');
        $taskStatus = $request->input('taskStatus');

        error_log(json_encode('BeforeSearch: ' . json_encode($tasks->count())));
        if (!!$search) {
            $tasks
                ->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }
        error_log(json_encode('AfterSearch: ' . json_encode($tasks->count())));

        if (!!$taskStatus) {
            $tasks->where('status', '=', $taskStatus);
        }
        error_log(json_encode('AfterFilter: ' . json_encode($tasks->count())));

        return response()->json($this->paginate($this->serializeUsers($tasks->get()), $request));
    }

    public function retrieve(Request $request, $id)
    {
        $task = Task::find($id);
        if ($task == null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // $admin = Roles::where('name', config('enums.roles')['ADMIN'])->first();
        if ($request->user()->hasPermission('task-list') || in_array(Auth::user()->id, [$task->created_by, $task->assigned_to])) {
            return response()->json($this->serializeUser($task));
        } else {
            return response()->json(['message' => 'Unauthorized. Only Creators or Assignees can view', 'type' => 'ERROR'], 401);
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
            'due_date' => 'date_format:Y-m-d\TH:i'
            // PHP date validator string
            // https://www.php.net/manual/en/datetime.createfromformat.php
        ]);

        $data = $request->only(['title', 'description', 'status', 'assigned_to', 'due_date']);

        if (array_key_exists('due_date', $data)) {
            $data['due_date'] = date("Y-m-d H:i:s", strtotime($data['due_date']));
        }

        $data['created_by'] = Auth::user()->id;
        $data['status'] = $TASK_STATUS[array_key_exists('status', $data)  ? $data['status'] : array_keys($TASK_STATUS)[0]];

        $task = Task::create($data);

        if ($task->assigned_to) {
            $this->pushEvent(['link' => "/task/" . $task->id, 'message' => 'You have been assigned the task ' . $task->id . ' by ' . User::find($task->created_by)->name], [$task->assigned_to]);

            $assignee = User::find($task->assigned_to);
            $assigner = User::find($task->created_by);
            $this->dispatch(new MailJob(new TaskAssignedMail($assignee, $assigner, $task), $assignee));
        }


        return response()->json(['message' => 'Task created successfully!', 'task' => $this->serializeUser($task)], 201);
    }

    public function update(Request $request, $id)
    {
        $TASK_STATUS = config('enums.task_status');
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        try {
            $this->validate($request, [
                'title' => 'min:1',
                'description' => 'nullable',
                'assigned_to' => 'nullable|sometimes|exists:users,id',
                // 'due_date' => 'date_format:"Y-m-d\TH:i"|nullable',
                // 'due_date' => 'date_format:Y-m-d\TH:i|nullable',

                'status' => 'in:' . join(",", array_keys($TASK_STATUS)),
            ]);
        } catch (\Throwable $th) {
            throw ($th);
            return response()->json(['message' => 'Validation error'], 400);
        }

        if (!in_array(Auth::user()->id, [$task->assigned_to, $task->created_by])) {
            return response()->json(['message' => 'Unauthorized. Only Creators or Assignees can update tasks', 401]);
        }
        $data = [];

        if ($task->created_by == Auth::user()->id) {
            $data = $request->only(['title', 'description', 'assigned_to', 'due_date']);
            if (array_key_exists('due_date', $data)) {
                $data['due_date'] = date("Y-m-d H:i:s", strtotime($data['due_date']));
            }
            if ($task->created_by != $task->assigned_to) {
                $this->pushEvent(['link' => "/task/" . $task->id, 'message' => 'Task ' . $task->id . ' was modified by the creator.'], [$task->assigned_to]);
            }
        }

        if ($task->assigned_to == Auth::user()->id && $request->has('status')) {
            $data['status'] = $TASK_STATUS[$request->input('status')];
            $this->pushEvent(['link' => "/task/" . $task->id, 'message' => 'The status of task ' . $task->id . ' was updated to ' . $data['status']], [$task->created_by]);
        }

        $task->update($data);
        return response()->json(['task' => $this->serializeUser($task), 'message' => 'Task updated successfully'], 201);
    }

    public function delete($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if (Auth::user()->id == $task->created_by) {
            $taskId = $task->id;
            $task->delete();
            $this->pushEvent(['link' => "/task/" . $taskId, 'message' => 'Task ' . $taskId . ' deleted successfully!'], [Auth::user()->id]);
            return response()->json(['message' => 'Task deleted successfully!'], 200);
        } else {
            return response()->json(['message' => 'You are not authorized to delete this task'], 401);;
        }
    }

    // public function pushNotif()
    // {
    //     $r = $this->pushEvent(["message" => "From the backend", 'type' => 'INFO'], Auth::user()->id);
    //     return response($r);
    // }
}
