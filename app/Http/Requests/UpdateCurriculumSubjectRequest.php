<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurriculumSubjectRequest extends FormRequest
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
        $id = $this->route('curriculum_subject');

        return [
            'major_id' => ['required', 'exists:majors,id'],
            'major_year_id' => [
                'required',
                Rule::exists('major_years', 'id')
                    ->where(fn ($q) => $q->where('major_id', $this->major_id)),
            ],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('curriculum_subjects', 'subject_id')
                    ->where(fn ($q) => $q->where('major_id', $this->major_id)
                        ->where('major_year_id', $this->major_year_id)
                        ->where('semester', $this->semester))
                    ->ignore($id),
            ],
            'semester' => ['required', 'integer', 'between:1,2'],
        ];
    }
}
