<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $room = $this->route('room');

        return [
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'category' => ['nullable', 'string', 'max:50'],
            'payer_id' => [
                'required',
                'uuid',
                'exists:participants,id,room_id,' . $room?->id,
            ],
            'splits' => ['required', 'array', 'min:1'],
            'splits.*.participant_id' => [
                'required',
                'uuid',
                'exists:participants,id,room_id,' . $room?->id,
            ],
            'splits.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateSplitsMatchTotal($validator);
        });
    }

    /**
     * Validate that splits sum equals the total amount.
     */
    protected function validateSplitsMatchTotal($validator): void
    {
        $total = (float) $this->input('amount', 0);
        $splitsSum = collect($this->input('splits', []))
            ->sum(fn ($split) => (float) ($split['amount'] ?? 0));

        if (abs($total - $splitsSum) > 0.01) {
            $validator->errors()->add(
                'splits',
                "La suma de los montos ($splitsSum) no coincide con el total ($total)."
            );
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Necesitás agregar una descripción.',
            'amount.required' => 'Necesitás ingresar el monto.',
            'amount.min' => 'El monto debe ser mayor a cero.',
            'payer_id.required' => 'Seleccioná quién pagó.',
            'payer_id.exists' => 'El pagador no existe en esta sala.',
            'splits.required' => 'Tenés que dividir el gasto entre al menos una persona.',
        ];
    }
}
