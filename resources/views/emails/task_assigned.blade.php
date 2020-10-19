<p>
Dear {$assignee},
You have been assigned <b>{$task->title}</b> by {$assigner}.<br />
@if($task->due_date)
The due date is {$task->due_date}.
@else
There is no due date assigned yet.
@endif
<br />
<a href="http://localhost:9000/task/{$task->id}">View task</a>
</p>