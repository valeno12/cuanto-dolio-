<?php

namespace App\Http\Controllers;

use App\Events\SettlementPaid;
use App\Models\Room;
use App\Models\Settlement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettlementController extends Controller
{
    /**
     * Get settlements for the current participant (personal view).
     */
    public function my(Request $request, Room $room): JsonResponse
    {
        $participant = $request->participant();

        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        if (!$room->is_locked) {
            abort(400, 'La sala debe estar cerrada.');
        }

        // Get settlements where I owe money
        $iOwe = Settlement::where('room_id', $room->id)
            ->where('from_participant_id', $participant->id)
            ->with('toParticipant:id,name,payment_alias')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'to' => [
                    'id' => $s->toParticipant->id,
                    'name' => $s->toParticipant->name,
                    'payment_alias' => $s->toParticipant->payment_alias,
                ],
                'amount' => round((float) $s->amount, 2),
                'is_paid' => $s->is_paid,
                'payment_method' => $s->payment_method,
                'paid_at' => $s->paid_at?->toISOString(),
            ]);

        // Get settlements where I'm owed money
        $theyOweMe = Settlement::where('room_id', $room->id)
            ->where('to_participant_id', $participant->id)
            ->with('fromParticipant:id,name,role')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'from' => [
                    'id' => $s->fromParticipant->id,
                    'name' => $s->fromParticipant->name,
                    'is_virtual' => $s->fromParticipant->isVirtual(),
                ],
                'amount' => round((float) $s->amount, 2),
                'is_paid' => $s->is_paid,
                'payment_method' => $s->payment_method,
                'paid_at' => $s->paid_at?->toISOString(),
            ]);

        // Also get virtual participants' settlements that admin can manage
        $virtualSettlements = [];
        if ($participant->isAdmin()) {
            $virtualIds = $room->participants()
                ->where('role', 'virtual')
                ->pluck('id');

            $virtualSettlements = Settlement::where('room_id', $room->id)
                ->whereIn('from_participant_id', $virtualIds)
                ->with(['fromParticipant:id,name', 'toParticipant:id,name,payment_alias'])
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'from' => [
                        'id' => $s->fromParticipant->id,
                        'name' => $s->fromParticipant->name,
                    ],
                    'to' => [
                        'id' => $s->toParticipant->id,
                        'name' => $s->toParticipant->name,
                        'payment_alias' => $s->toParticipant->payment_alias,
                    ],
                    'amount' => round((float) $s->amount, 2),
                    'is_paid' => $s->is_paid,
                    'payment_method' => $s->payment_method,
                    'paid_at' => $s->paid_at?->toISOString(),
                ]);
        }

        // Calculate totals
        $totalIOwe = $iOwe->where('is_paid', false)->sum('amount');
        $totalOwedToMe = $theyOweMe->where('is_paid', false)->sum('amount');

        return response()->json([
            'i_owe' => $iOwe->values(),
            'they_owe_me' => $theyOweMe->values(),
            'virtual_settlements' => collect($virtualSettlements)->values(),
            'total_i_owe' => round($totalIOwe, 2),
            'total_owed_to_me' => round($totalOwedToMe, 2),
            'my_alias' => $participant->payment_alias,
        ]);
    }

    /**
     * Mark a settlement as paid.
     */
    public function markAsPaid(Request $request, Room $room, Settlement $settlement): JsonResponse
    {
        $participant = $request->participant();

        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        // Can mark as paid if: I'm the debtor, I'm the creditor, or I'm admin (for virtuals)
        $canMark = $settlement->from_participant_id === $participant->id
            || $settlement->to_participant_id === $participant->id
            || $participant->isAdmin();

        if (!$canMark) {
            abort(403, 'No podés marcar este pago.');
        }

        if ($settlement->is_paid) {
            return response()->json(['message' => 'Este pago ya fue marcado.'], 400);
        }

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'transfer'])],
        ]);

        $settlement->markAsPaid($validated['payment_method']);

        // Broadcast to room
        broadcast(new SettlementPaid($settlement))->toOthers();

        return response()->json([
            'message' => '¡Pago registrado!',
            'settlement' => $settlement->fresh(),
        ]);
    }

    /**
     * Update current participant's payment alias.
     */
    public function updateAlias(Request $request, Room $room): \Illuminate\Http\RedirectResponse
    {
        $participant = $request->participant();

        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        $validated = $request->validate([
            'payment_alias' => ['nullable', 'string', 'max:100'],
        ]);

        $participant->update(['payment_alias' => $validated['payment_alias']]);

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Alias actualizado.');
    }

    /**
     * Get all settlements for the room (for sharing/overview).
     */
    public function all(Request $request, Room $room): JsonResponse
    {
        $participant = $request->participant();

        if (!$participant || $participant->room_id !== $room->id) {
            abort(403, 'No pertenecés a esta sala.');
        }

        if (!$room->is_locked) {
            abort(400, 'La sala debe estar cerrada.');
        }

        $settlements = Settlement::where('room_id', $room->id)
            ->with(['fromParticipant:id,name', 'toParticipant:id,name'])
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'from' => $s->fromParticipant->name,
                'to' => $s->toParticipant->name,
                'amount' => round((float) $s->amount, 2),
                'is_paid' => $s->is_paid,
            ]);

        $totalExpenses = $room->expenses()->sum('amount');
        $pendingCount = $settlements->where('is_paid', false)->count();
        $paidCount = $settlements->where('is_paid', true)->count();

        return response()->json([
            'settlements' => $settlements->values(),
            'total_expenses' => round((float) $totalExpenses, 2),
            'pending_count' => $pendingCount,
            'paid_count' => $paidCount,
            'room_code' => $room->code,
        ]);
    }
}

