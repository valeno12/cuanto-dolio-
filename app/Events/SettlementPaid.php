<?php

namespace App\Events;

use App\Models\Settlement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettlementPaid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Settlement $settlement
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.' . $this->settlement->room_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'settlement_id' => $this->settlement->id,
            'from_participant_id' => $this->settlement->from_participant_id,
            'to_participant_id' => $this->settlement->to_participant_id,
            'is_paid' => true,
            'payment_method' => $this->settlement->payment_method,
            'paid_at' => $this->settlement->paid_at?->toISOString(),
        ];
    }
}
