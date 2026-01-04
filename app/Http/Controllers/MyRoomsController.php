<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyRoomsController extends Controller
{
    public function __construct(
        protected ParticipantSessionService $sessionService
    ) {}

    /**
     * Get all rooms where the user has a valid session token.
     */
    public function index(Request $request): JsonResponse
    {
        $rooms = [];
        $cookies = $request->cookies->all();
        $prefix = ParticipantSessionService::COOKIE_PREFIX;

        foreach ($cookies as $name => $token) {
            if (!str_starts_with($name, $prefix) || !$token) {
                continue;
            }

            // Find participant by token
            $participant = Participant::where('session_token', $token)->first();

            if (!$participant) {
                continue;
            }

            // Load room with counts
            $room = Room::active()
                ->where('id', $participant->room_id)
                ->withCount(['participants', 'expenses'])
                ->first();

            if (!$room) {
                continue;
            }

            $rooms[] = [
                'id' => $room->id,
                'code' => $room->code,
                'name' => $room->name,
                'is_locked' => $room->is_locked,
                'expires_at' => $room->expires_at?->toISOString(),
                'participant_count' => $room->participants_count,
                'expense_count' => $room->expenses_count,
                'my_name' => $participant->name,
                'my_role' => $participant->role->value,
                'created_at' => $room->created_at->toISOString(),
            ];
        }

        // Sort by most recently created
        usort($rooms, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return response()->json([
            'rooms' => $rooms,
        ]);
    }
}
