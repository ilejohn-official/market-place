<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('PATCH')) {
            return [
                'status' => 'required|in:accepted,completed,missed,cancelled',
            ];
        }

        return [
            'call_type' => 'required|in:audio,video',
        ];
    }
}
