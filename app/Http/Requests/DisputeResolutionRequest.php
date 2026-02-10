<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisputeResolutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resolution_decision' => 'required|in:refund_to_buyer,release_to_seller',
            'notes' => 'sometimes|string|max:5000',
        ];
    }
}
