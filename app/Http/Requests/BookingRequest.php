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
        if ($this->isMethod('PUT')) {
            return [
                'status' => 'sometimes|in:pending_negotiation,in_progress,pending_approval,completed,disputed,cancelled,refunded',
            ];
        }

        return [
            'service_id' => 'required|integer|exists:services,id',
            'seller_id' => 'required|integer|exists:users,id',
            'proposed_amount' => 'required|numeric|min:0.01',
            'description' => 'sometimes|string|max:5000',
        ];
    }
}
