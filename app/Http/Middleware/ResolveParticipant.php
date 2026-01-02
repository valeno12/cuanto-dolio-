<?php

namespace App\Http\Middleware;

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
     * Resolves the participant from the session token and attaches it to the request.
     * Does NOT block requests without a token - some routes are public.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $participant = $this->sessionService->resolveFromRequest($request);

        // Attach participant to request using a macro-like approach
        $request->attributes->set('participant', $participant);

        return $next($request);
    }
}
