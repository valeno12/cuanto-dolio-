<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $participant = $this->participant();
        $room = $this->route('room');
        $expense = $this->route('expense');

        // Must be a participant of this room
        if (!$participant || !$room || $participant->room_id !== $room->id) {
            return false;
        }

        // Must be the payer of this expense OR an admin
        return $expense->payer_id === $participant->id || $participant->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
