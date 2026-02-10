<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|integer|exists:services,id',
            'seller_id' => 'required|integer|exists:users,id',
            'proposed_amount' => 'required|numeric|min:0.01',
            'negotiation_notes' => 'sometimes|string|max:5000',
        ];
    }
}
