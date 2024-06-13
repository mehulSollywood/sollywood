<?php

namespace App\Events\Mails;

use App\Models\EmailTemplate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSendByTemplate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?EmailTemplate $emailTemplate;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(?EmailTemplate $emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
