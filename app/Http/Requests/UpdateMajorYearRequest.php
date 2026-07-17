<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('academic_year');

        return [
            'major_id' => ['required', 'exists:majors,id'],
            'year_number' => [
                'required',
                'integer',
                'between:1,10',
                Rule::unique('major_years', 'year_number')
                    ->where(fn ($q) => $q->where('major_id', $this->major_id))
                    ->ignore($id),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
