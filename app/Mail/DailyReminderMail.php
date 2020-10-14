<?php

namespace App\Mail;

use App\Models\User;
use App\Models\VerifyUser as Token;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeMail extends Mailable
{
    protected $user;
    protected $token;

    use Queueable, SerializesModels;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    //build the message.
    public function build()
    {
        return $this->view('emails.welcome')
            ->with([
                'name' => $this->user['name'],
                'email' => $this->user['email'],
                'token' => $this->token
            ]);
    }
}
