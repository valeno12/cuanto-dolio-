<?php

namespace App\Http\Requests;

use App\Enums\ParticipantRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateVirtualParticipantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $participant = $this->participant();
        $room = $this->route('room');

        // Only admin can create virtual participants
        return $participant 
            && $room 
            && $participant->room_id === $room->id 
            && $participant->role === ParticipantRole::Admin;
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
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('participants', 'name')->where('room_id', $room?->id),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'name.max' => 'El nombre no puede tener más de 50 caracteres.',
            'name.unique' => 'Ya hay alguien con ese nombre en la sala.',
        ];
    }
}
