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
     * The cookie name for storing the participant token.
     */
    public const COOKIE_NAME = 'participant_token';

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
     * Resolve the participant from the request (cookie or header).
     */
    public function resolveFromRequest(Request $request): ?Participant
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return null;
        }

        return Participant::where('session_token', $token)->first();
    }

    /**
     * Get the token from request (cookie first, then header).
     */
    public function getTokenFromRequest(Request $request): ?string
    {
        return $request->cookie(self::COOKIE_NAME)
            ?? $request->header(self::HEADER_NAME);
    }

    /**
     * Create a new participant in a room with a fresh token.
     */
    public function createParticipant(
        Room $room,
        string $name,
        ParticipantRole $role = ParticipantRole::Member
    ): Participant {
        $token = $role === ParticipantRole::Virtual ? null : $this->generateToken();

        return $room->participants()->create([
            'name' => $name,
            'role' => $role,
            'session_token' => $token,
        ]);
    }

    /**
     * Create a cookie response with the participant token.
     */
    public function makeCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name: self::COOKIE_NAME,
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
