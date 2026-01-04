<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\ParticipantRole;
use Carbon\Carbon;

class Room extends Model
{
    use HasFactory, HasUuids;

    /**
     * Room expiration in days.
     */
    public const EXPIRATION_DAYS = 60;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'is_locked',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Room $room) {
            if (!$room->expires_at) {
                $room->expires_at = Carbon::now()->addDays(self::EXPIRATION_DAYS);
            }
        });
    }

    /**
     * Check if the room has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope to get only active (non-expired) rooms.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get only expired rooms.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    /**
     * Get all participants in this room.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get all expenses in this room.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the admin (creator) of this room.
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Participant::class)->where('role', ParticipantRole::Admin);
    }

    /**
     * Get all settlements for this room.
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }
}
