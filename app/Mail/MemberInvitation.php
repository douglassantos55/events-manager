<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $member;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $member)
    {
        $this->member = $member;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->to($this->member->email);
        return $this->view('mail/member/invitation');
    }
}
