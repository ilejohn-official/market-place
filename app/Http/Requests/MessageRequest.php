<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message_content' => 'required|string|max:5000',
            'file_attachment_url' => 'sometimes|string|max:2048',
        ];
    }
}
