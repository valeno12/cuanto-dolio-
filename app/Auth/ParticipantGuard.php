<?php

namespace App\Auth;

use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantSessionService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class ParticipantGuard implements Guard
{
    protected ?Participant $participant = null;
    protected bool $resolved = false;

    public function __construct(
        protected Request $request,
        protected ParticipantSessionService $sessionService
    ) {}

    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Authenticatable
    {
        if (!$this->resolved) {
            // Try to get room from route
            $roomCode = $this->request->route('room')?->code ?? $this->request->route('room');
            $room = null;
            
            if ($roomCode) {
                $room = $roomCode instanceof Room ? $roomCode : Room::where('code', $roomCode)->first();
            }
            
            // Fallback: extract room from broadcasting channel_name (for /broadcasting/auth)
            // The channel_name comes as "private-room.{roomId}" in the POST body
            if (!$room && $this->request->has('channel_name')) {
                $channelName = $this->request->input('channel_name');
                if (preg_match('/^(?:private-|presence-)?room\.(.+)$/', $channelName, $matches)) {
                    $room = Room::find($matches[1]);
                }
            }
            
            $this->participant = $this->sessionService->resolveFromRequest($this->request, $room);
            $this->resolved = true;
        }

        return $this->participant;
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): ?string
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool
    {
        // We don't validate credentials - we use session tokens from cookies
        return false;
    }

    /**
     * Determine if the guard has a user instance.
     */
    public function hasUser(): bool
    {
        return $this->participant !== null;
    }

    /**
     * Set the current user.
     */
    public function setUser(Authenticatable $user): void
    {
        if ($user instanceof Participant) {
            $this->participant = $user;
            $this->resolved = true;
        }
    }
}
