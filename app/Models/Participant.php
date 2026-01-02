<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'name',
        'session_token',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'session_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ParticipantRole::class,
        ];
    }

    /**
     * Get the room this participant belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get all expenses paid by this participant.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'payer_id');
    }

    /**
     * Get all expense splits where this participant owes money.
     */
    public function splits(): HasMany
    {
        return $this->hasMany(ExpenseSplit::class);
    }

    /**
     * Check if this participant is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === ParticipantRole::Admin;
    }

    /**
     * Check if this participant is virtual (no real device).
     */
    public function isVirtual(): bool
    {
        return $this->role === ParticipantRole::Virtual;
    }
}
