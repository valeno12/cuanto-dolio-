<?php

namespace App\Http\Controllers;

use App\Enums\ParticipantRole;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\CreateVirtualParticipantRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Models\Room;
use App\Services\ParticipantSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function __construct(
        protected ParticipantSessionService $sessionService
    ) {}

    /**
     * Show the landing page with "Create Room" form.
     */
    public function create(): Response
    {
        return Inertia::render('Home');
    }

    /**
     * Create a new room and the admin participant.
     */
    public function store(CreateRoomRequest $request): RedirectResponse
    {
        // Generate a unique 6-character code
        $code = $this->generateUniqueCode();

        $room = Room::create(['code' => $code]);

        // Create the admin participant
        $admin = $this->sessionService->createParticipant(
            room: $room,
            name: $request->validated('nickname'),
            role: ParticipantRole::Admin
        );

        // Redirect to room with the session cookie
        return redirect()
            ->route('rooms.show', $room->code)
            ->withCookie($this->sessionService->makeCookie($admin->session_token));
    }

    /**
     * Show a room (if participant has valid token for this room).
     */
    public function show(Request $request, Room $room): Response|RedirectResponse
    {
        $participant = $request->participant();

        // If no valid participant for THIS room, redirect to join
        if (!$this->sessionService->participantBelongsToRoom($participant, $room)) {
            return redirect()->route('rooms.join', $room->code);
        }

        // Load room with participants and expenses
        $room->load([
            'participants',
            'expenses.payer',
            'expenses.splits.participant',
        ]);

        return Inertia::render('Room/Show', [
            'room' => $room,
            'currentParticipant' => $participant,
        ]);
    }

    /**
     * Show the join form for a room.
     */
    public function join(Request $request, Room $room): Response|RedirectResponse
    {
        $participant = $request->participant();

        // If already a participant of this room, go directly to room
        if ($this->sessionService->participantBelongsToRoom($participant, $room)) {
            return redirect()->route('rooms.show', $room->code);
        }

        // If room is locked, can't join
        if ($room->is_locked) {
            return Inertia::render('Room/Locked', [
                'room' => $room->only(['code']),
            ]);
        }

        return Inertia::render('Room/Join', [
            'room' => $room->only(['id', 'code']),
        ]);
    }

    /**
     * Create a new participant (join the room).
     */
    public function storeParticipant(JoinRoomRequest $request, Room $room): RedirectResponse
    {
        $participant = $this->sessionService->createParticipant(
            room: $room,
            name: $request->validated('nickname'),
            role: ParticipantRole::Member
        );

        return redirect()
            ->route('rooms.show', $room->code)
            ->withCookie($this->sessionService->makeCookie($participant->session_token));
    }

    /**
     * Create a virtual participant (admin only).
     */
    public function storeVirtualParticipant(CreateVirtualParticipantRequest $request, Room $room): RedirectResponse
    {
        $this->sessionService->createParticipant(
            room: $room,
            name: $request->validated('name'),
            role: ParticipantRole::Virtual
        );

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Participante agregado.');
    }

    /**
     * Generate a unique 6-character room code.
     */
    protected function generateUniqueCode(): string
    {
        do {
            // Generate alphanumeric code (easy to type, no confusing chars)
            $code = strtoupper(Str::random(6));
            // Remove confusing characters
            $code = str_replace(['0', 'O', 'I', 'L', '1'], ['X', 'Y', 'Z', 'W', '2'], $code);
        } while (Room::where('code', $code)->exists());

        return $code;
    }
}
