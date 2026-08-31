<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'major_year_id' => ['required', 'exists:major_years,id'],
            'semester' => ['required', 'integer', 'in:1,2'],
            // Image is optional on update — keep the existing one when omitted.
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
        ];
    }
}
