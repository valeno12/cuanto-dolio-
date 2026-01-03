<?php

namespace App\Services;

use App\Enums\ParticipantRole;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParticipantSessionService
{
    /**
     * The cookie name prefix for storing the participant token.
     * Full cookie name will be: participant_token_{room_code}
     */
    public const COOKIE_PREFIX = 'participant_token_';

    /**
     * The header name for the participant token (alternative to cookie).
     */
    public const HEADER_NAME = 'X-Participant-Token';

    /**
     * Cookie lifetime in minutes (30 days).
     */
    public const COOKIE_LIFETIME = 60 * 24 * 30;

    /**
     * Generate a new unique session token.
     */
    public function generateToken(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Get the cookie name for a specific room.
     */
    public function getCookieName(string $roomCode): string
    {
        return self::COOKIE_PREFIX . $roomCode;
    }

    /**
     * Resolve the participant from the request for a specific room.
     */
    public function resolveFromRequest(Request $request, ?Room $room = null): ?Participant
    {
        // If room is provided, look for room-specific cookie
        if ($room) {
            $token = $this->getTokenFromRequest($request, $room->code);
            
            if ($token) {
                $participant = Participant::where('session_token', $token)
                    ->where('room_id', $room->id)
                    ->first();
                
                if ($participant) {
                    return $participant;
                }
            }
        }

        // Fallback: try header
        $headerToken = $request->header(self::HEADER_NAME);
        if ($headerToken) {
            return Participant::where('session_token', $headerToken)->first();
        }

        return null;
    }

    /**
     * Get the token from request for a specific room.
     */
    public function getTokenFromRequest(Request $request, ?string $roomCode = null): ?string
    {
        if ($roomCode) {
            return $request->cookie($this->getCookieName($roomCode));
        }

        return $request->header(self::HEADER_NAME);
    }

    /**
     * Create a new participant in a room with a fresh token.
     */
    public function createParticipant(
        Room $room,
        string $name,
        ParticipantRole $role = ParticipantRole::Member,
        ?string $paymentAlias = null
    ): Participant {
        $token = $role === ParticipantRole::Virtual ? null : $this->generateToken();

        return $room->participants()->create([
            'name' => $name,
            'role' => $role,
            'session_token' => $token,
            'payment_alias' => $paymentAlias,
        ]);
    }

    /**
     * Create a cookie response with the participant token for a specific room.
     */
    public function makeCookie(string $token, string $roomCode): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name: $this->getCookieName($roomCode),
            value: $token,
            minutes: self::COOKIE_LIFETIME,
            sameSite: 'lax'
        );
    }

    /**
     * Check if a participant belongs to a specific room.
     */
    public function participantBelongsToRoom(?Participant $participant, Room $room): bool
    {
        return $participant && $participant->room_id === $room->id;
    }
}
