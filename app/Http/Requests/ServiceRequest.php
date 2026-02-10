<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:5000',
                'category' => 'sometimes|string|max:100',
                'price' => 'sometimes|numeric|min:0.01',
                'estimated_days' => 'sometimes|integer|min:1',
                'tags' => 'sometimes|array',
                'tags.*' => 'string|max:50',
                'is_active' => 'sometimes|boolean',
            ];
        }

        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01',
            'estimated_days' => 'required|integer|min:1',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
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
            'title.required' => 'Service title is required',
            'title.max' => 'Service title cannot exceed 255 characters',
            'description.required' => 'Service description is required',
            'description.max' => 'Service description cannot exceed 5000 characters',
            'category.required' => 'Service category is required',
            'price.required' => 'Service price is required',
            'price.numeric' => 'Service price must be a number',
            'price.min' => 'Service price must be greater than 0',
            'estimated_days.required' => 'Estimated days is required',
            'estimated_days.integer' => 'Estimated days must be an integer',
            'estimated_days.min' => 'Estimated days must be at least 1',
        ];
    }
}
