<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'publication_date' => ['required', 'date', 'before_or_equal:today'],
            'url' => ['required', 'url', 'max:2048'],
            'read_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The article title is required.',
            'title.max' => 'The article title cannot exceed 255 characters.',
            'publication_date.required' => 'The publication date is required.',
            'publication_date.before_or_equal' => 'The publication date cannot be in the future.',
            'url.required' => 'The article URL is required.',
            'url.url' => 'Please enter a valid URL.',
            'url.max' => 'The URL cannot exceed 2048 characters.',
            'read_date.required' => 'The read date is required.',
            'read_date.before_or_equal' => 'The read date cannot be in the future.',
        ];
    }
}
