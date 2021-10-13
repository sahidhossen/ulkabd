<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class BroadcastTrainingStatus implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trainingStatus;

    public $user;

    /**
     * BroadcastTrainingStatus constructor.
     * @param $trainingStatus
     * @param User $user
     */
    public function __construct($trainingStatus, User $user )
    {
        $this->trainingStatus = $trainingStatus;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
//        Log::info("user: ". $this->user );
        return new PrivateChannel('channel-agent-train_'.$this->user->id );
    }
}
