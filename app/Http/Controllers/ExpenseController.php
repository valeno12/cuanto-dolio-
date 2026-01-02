<?php

namespace App\Http\Controllers;

use App\Events\ExpenseCreated;
use App\Events\ExpenseDeleted;
use App\Events\ExpenseUpdated;
use App\Http\Requests\CreateExpenseRequest;
use App\Http\Requests\DeleteExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    /**
     * Create a new expense with its splits.
     */
    public function store(CreateExpenseRequest $request, Room $room): RedirectResponse
    {
        $validated = $request->validated();

        // Create the expense
        $expense = Expense::create([
            'room_id' => $room->id,
            'payer_id' => $validated['payer_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        // Create the splits
        foreach ($validated['splits'] as $splitData) {
            $expense->splits()->create([
                'participant_id' => $splitData['participant_id'],
                'amount_owed' => $splitData['amount'],
            ]);
        }

        // Broadcast the event
        broadcast(new ExpenseCreated($expense))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Gasto agregado correctamente.');
    }

    /**
     * Update an existing expense.
     */
    public function update(UpdateExpenseRequest $request, Room $room, Expense $expense): RedirectResponse
    {
        $validated = $request->validated();

        // Update the expense
        $expense->update([
            'payer_id' => $validated['payer_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        // Delete old splits and create new ones
        $expense->splits()->delete();
        foreach ($validated['splits'] as $splitData) {
            $expense->splits()->create([
                'participant_id' => $splitData['participant_id'],
                'amount_owed' => $splitData['amount'],
            ]);
        }

        // Broadcast the event
        broadcast(new ExpenseUpdated($expense->fresh()))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Gasto actualizado.');
    }

    /**
     * Delete an expense.
     */
    public function destroy(DeleteExpenseRequest $request, Room $room, Expense $expense): RedirectResponse
    {
        $expenseId = $expense->id;
        $roomId = $room->id;
        
        $expense->delete();

        // Broadcast the event
        broadcast(new ExpenseDeleted($expenseId, $roomId))->toOthers();

        return redirect()
            ->route('rooms.show', $room->code)
            ->with('success', 'Gasto eliminado.');
    }
}


