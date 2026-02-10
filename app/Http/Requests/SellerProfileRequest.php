<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSeller();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('PUT')) {
            return [
                'hourly_rate' => 'sometimes|numeric|min:0.01',
                'experience_level' => 'sometimes|in:beginner,intermediate,expert',
            ];
        }

        return [
            'hourly_rate' => 'required|numeric|min:0.01',
            'experience_level' => 'required|in:beginner,intermediate,expert',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hourly_rate.required' => 'Hourly rate is required',
            'hourly_rate.numeric' => 'Hourly rate must be a number',
            'hourly_rate.min' => 'Hourly rate must be greater than 0',
            'experience_level.required' => 'Experience level is required',
            'experience_level.in' => 'Experience level must be beginner, intermediate, or expert',
        ];
    }
}
