<?php

namespace App\Http\Controllers;

use App\Enums\ParticipantRole;
use App\Events\ParticipantJoined;
use App\Events\RoomLocked;
use App\Events\RoomUnlocked;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\CreateVirtualParticipantRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Models\Room;
use App\Models\Settlement;
use App\Services\DebtSimplificationService;
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

        $room = Room::create([
            'code' => $code,
            'name' => $request->validated('room_name'),
        ]);

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
            'room' => $room->only(['id', 'code', 'name']),
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
            role: ParticipantRole::Member,
            paymentAlias: $request->validated('payment_alias')
        );

        // Broadcast to other participants in the room
        broadcast(new ParticipantJoined($participant))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->withCookie($this->sessionService->makeCookie($participant->session_token));
    }

    /**
     * Create a virtual participant (admin only).
     */
    public function storeVirtualParticipant(CreateVirtualParticipantRequest $request, Room $room): RedirectResponse
    {
        $participant = $this->sessionService->createParticipant(
            room: $room,
            name: $request->validated('name'),
            role: ParticipantRole::Virtual
        );

        // Broadcast to other participants in the room
        broadcast(new ParticipantJoined($participant))->toOthers();

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

    /**
     * Lock a room (admin only). Prevents adding new expenses.
     */
    public function lock(Request $request, Room $room): RedirectResponse
    {
        $participant = $request->participant();

        // Verify participant is admin of this room
        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        if (!$participant->isAdmin()) {
            abort(403, 'Solo el admin puede cerrar la sala.');
        }

        if ($room->is_locked) {
            return redirect()
                ->route('rooms.show', $room->code)
                ->with('info', 'La sala ya está cerrada.');
        }

        // Calculate settlements and save to database
        $debtService = app(DebtSimplificationService::class);
        $settlements = $debtService->calculate($room);

        foreach ($settlements as $settlement) {
            Settlement::create([
                'room_id' => $room->id,
                'from_participant_id' => $settlement['from']['id'],
                'to_participant_id' => $settlement['to']['id'],
                'amount' => $settlement['amount'],
            ]);
        }

        $room->update(['is_locked' => true]);

        // Broadcast to all participants
        broadcast(new RoomLocked($room))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Sala cerrada. ¡Ahora pueden ver quién le paga a quién!');
    }

    /**
     * Unlock/reopen a room (admin only). Allows adding new expenses again.
     */
    public function unlock(Request $request, Room $room): RedirectResponse
    {
        $participant = $request->participant();

        // Verify participant is admin of this room
        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        if (!$participant->isAdmin()) {
            abort(403, 'Solo el admin puede reabrir la sala.');
        }

        if (!$room->is_locked) {
            return redirect()
                ->route('rooms.show', $room->code)
                ->with('info', 'La sala ya está abierta.');
        }

        // Delete existing settlements (they will be recalculated when locked again)
        $room->settlements()->delete();

        $room->update(['is_locked' => false]);

        // Broadcast to all participants
        broadcast(new RoomUnlocked($room))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Sala reabierta. Podés seguir agregando gastos.');
    }

    /**
     * Delete a participant (admin only, cannot delete self or other admins).
     */
    public function destroyParticipant(Request $request, Room $room, \App\Models\Participant $participant): RedirectResponse
    {
        $currentParticipant = $request->participant();

        // Verify current user is admin of this room
        if (!$currentParticipant || $currentParticipant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        if (!$currentParticipant->isAdmin()) {
            abort(403, 'Solo el admin puede eliminar participantes.');
        }

        if ($room->is_locked) {
            abort(403, 'No se pueden eliminar participantes de una sala cerrada.');
        }

        // Cannot delete yourself
        if ($participant->id === $currentParticipant->id) {
            abort(403, 'No podés eliminarte a vos mismo.');
        }

        // Cannot delete another admin
        if ($participant->isAdmin()) {
            abort(403, 'No podés eliminar a otro admin.');
        }

        // Verify participant belongs to this room
        if ($participant->room_id !== $room->id) {
            abort(404, 'Participante no encontrado.');
        }

        // Delete associated expense splits first
        $participant->splits()->delete();
        
        // Delete expenses where this participant was the payer
        foreach ($participant->expenses as $expense) {
            $expense->splits()->delete();
            $expense->delete();
        }

        // Delete the participant
        $participant->delete();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', "Participante {$participant->name} eliminado.");
    }
}
