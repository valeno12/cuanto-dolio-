<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'from_participant_id',
        'to_participant_id',
        'amount',
        'is_paid',
        'payment_method',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the room this settlement belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the participant who owes money (debtor).
     */
    public function fromParticipant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'from_participant_id');
    }

    /**
     * Get the participant who is owed money (creditor).
     */
    public function toParticipant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'to_participant_id');
    }

    /**
     * Mark this settlement as paid.
     */
    public function markAsPaid(string $method): void
    {
        $this->update([
            'is_paid' => true,
            'payment_method' => $method,
            'paid_at' => now(),
        ]);
    }
}
