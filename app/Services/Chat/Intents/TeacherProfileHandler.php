<?php

namespace App\Services\Chat\Intents;

use App\Models\Teacher;
use App\Services\Chat\Contracts\IntentHandler;
use App\Services\Chat\Intents\Concerns\PositionMatching;

/**
 * A focused profile view for one teacher — returns the full contact card and
 * their subject list. Used when the user asks about a specific person.
 */
class TeacherProfileHandler implements IntentHandler
{
    use PositionMatching;

    public function name(): string
    {
        return 'teacher_profile';
    }

    public function handle(array $filters): array
    {
        $teacher = trim((string) ($filters['teacher'] ?? ''));
        $position = trim((string) ($filters['position'] ?? ''));

        if ($teacher === '' && $position === '') {
            return [];
        }

        $query = Teacher::query()
            ->with(['department:id,name', 'teacherAssignments.curriculumSubject.subject:id,code,name']);

        if ($teacher !== '') {
            $query->where('name', 'like', "%{$teacher}%");
        }

        // The person may be identified by role rather than by name — "who is
        // the rector of KTU?" names nobody.
        if ($position !== '') {
            $this->wherePositionMatches($query, $position);
        }

        $model = $query->first();

        if (! $model) {
            return [];
        }

        $subjects = $model->teacherAssignments
            ->map(fn ($a) => $a->curriculumSubject?->subject)
            ->filter()
            ->unique('id')
            ->map(fn ($s) => ['code' => $s->code, 'name' => $s->name])
            ->values()
            ->all();

        return [
            'name' => $model->name,
            'department' => $model->department?->name,
            'position' => $model->position,
            'degree' => $model->degree,
            'email' => $model->email,
            'phone' => $model->phone,
            'bio' => $model->bio,
            'image_url' => $model->image_url,
            'subjects' => $subjects,
        ];
    }
}
