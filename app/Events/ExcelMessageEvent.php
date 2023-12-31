<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExcelMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelName;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($channelName, $message)
    {
        $this->channelName = $channelName;
        $this->message = $message;
        // $this->message = [
        //     'tipo' => 'exito',
        //     'mensaje' => $message,
        //     'titulo' => 'Informe generado con exito!',
        //     'autoclose' => false
        // ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new Channel($this->channelName),
        ];
    }

    
    public function broadcastAs()
	{
		return 'excel';
	}
}
