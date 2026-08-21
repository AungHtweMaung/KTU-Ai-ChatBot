<?php

namespace App\Services\Chat\Intents;

use App\Models\Teacher;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * "Who teaches X?" / "Show me teachers in the Y department" — matches
 * teachers by subject, name, department, or position.
 */
class TeacherSearchHandler implements IntentHandler
{
    public function name(): string
    {
        return 'teacher_search';
    }

    public function handle(array $filters): array
    {
        $subject = trim((string) ($filters['subject'] ?? ''));
        $teacher = trim((string) ($filters['teacher'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));

        $query = Teacher::query();

        if ($teacher !== '') {
            $query->where('name', 'like', "%{$teacher}%");
        }

        if ($department !== '') {
            $query->whereHas('department', fn ($q) => $q->where('name', 'like', "%{$department}%"));
        }

        if ($subject !== '') {
            // A teacher "teaches" a subject via curriculum_subjects → subjects.
            $query->whereHas(
                'teacherAssignments.curriculumSubject.subject',
                fn ($q) => $q->where('name', 'like', "%{$subject}%")
                    ->orWhere('code', 'like', "%{$subject}%"),
            );
        }

        $total = (clone $query)->count();

        $items = $query
            ->with('department:id,name')
            ->limit(10)
            ->get()
            ->map(fn (Teacher $t) => [
                'name' => $t->name,
                'department' => $t->department?->name,
                'position' => $t->position,
                'email' => $t->email,
                'image_url' => $t->image_url,
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
