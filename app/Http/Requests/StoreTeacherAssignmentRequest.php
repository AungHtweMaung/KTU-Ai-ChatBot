<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAssignmentRequest extends FormRequest
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
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'curriculum_subject_id' => [
                'required',
                'exists:curriculum_subjects,id',
                Rule::unique('teacher_assignments', 'curriculum_subject_id')
                    ->where(fn ($q) => $q->where('teacher_id', $this->teacher_id)
                        ->where('school_year', $this->school_year)),
            ],
            'school_year' => ['required', 'string', 'max:20'],
        ];
    }
}
