<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedMail extends Mailable
{
    protected $assignee;
    protected $assigner;
    protected $task;

    use Queueable, SerializesModels;

    public function __construct($assignee, $assigner, $task)
    {
        $this->assignee = $assignee;
        $this->assigner = $assigner;
        $this->task = $task;
    }

    //build the message.
    public function build()
    {
        return $this->view('emails.welcome')
            ->with([
                'assigner' => $this->assigner['name'],
                'assignee' => $this->assignee['name'],
                'task' => $this->task
            ]);
    }
}
