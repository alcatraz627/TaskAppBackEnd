<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Mail\Mailable;

use Illuminate\Support\Facades\Mail;

class MailJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $user;
    protected $mailType;

    public function __construct(Mailable $mailType, User $user)
    {
        $this->user = $user;
        $this->mailType = $mailType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user)->send($this->mailType);
    }
}
