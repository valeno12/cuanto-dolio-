<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $room = $this->route('room');
        
        // Can't join a locked room
        return $room && !$room->is_locked;
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
            'nickname' => [
                'required',
                'string',
                'max:50',
                // Nickname must be unique within this room
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
            'nickname.required' => 'Necesitás ingresar un nombre.',
            'nickname.max' => 'El nombre no puede tener más de 50 caracteres.',
            'nickname.unique' => 'Ya hay alguien con ese nombre en la sala.',
        ];
    }
}
