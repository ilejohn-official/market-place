<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'evidence_attachments' => 'sometimes|array',
            'evidence_attachments.*' => 'string|max:2048',
        ];
    }
}
