<?php

namespace App\Http\Middleware;

use App\Models\Room;
use App\Services\ParticipantSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveParticipant
{
    public function __construct(
        protected ParticipantSessionService $sessionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Resolves the participant from the session token for the specific room.
     * Does NOT block requests without a token - some routes are public.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $participant = null;

        // Try to get the room from route parameters
        $roomCode = $request->route('room')?->code ?? $request->route('room');
        
        if ($roomCode) {
            $room = $roomCode instanceof Room ? $roomCode : Room::where('code', $roomCode)->first();
            
            if ($room) {
                $participant = $this->sessionService->resolveFromRequest($request, $room);
            }
        }

        // Attach participant to request
        $request->attributes->set('participant', $participant);

        return $next($request);
    }
}
