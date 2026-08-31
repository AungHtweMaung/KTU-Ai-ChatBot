<?php

namespace App\Services\Chat\Intents;

use App\Models\Teacher;
use App\Services\Chat\Contracts\IntentHandler;
use App\Services\Chat\Intents\Concerns\LooseMatching;

/**
 * "Who teaches X?" / "Show me teachers in the Y department" — matches
 * teachers by subject, name, department, or position.
 */
class TeacherSearchHandler implements IntentHandler
{
    use LooseMatching;

    public function name(): string
    {
        return 'teacher_search';
    }

    public function handle(array $filters): array
    {
        $subject = trim((string) ($filters['subject'] ?? ''));
        $teacher = trim((string) ($filters['teacher'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));
        $major = trim((string) ($filters['major'] ?? ''));
        $majorYear = trim((string) ($filters['major_year'] ?? ''));

        $query = Teacher::query();

        if ($teacher !== '') {
            $query->where('name', 'like', "%{$teacher}%");
        }

        // When the question carries academic context ("who teaches English in
        // first year CEIT"), the subject/year/department all describe the CLASS
        // being taught, not the teacher's home department. Foundation staff
        // (English, Maths…) often belong to another department yet teach a
        // given program's class, so we must constrain the teaching ASSIGNMENT,
        // not teacher.department — otherwise they are wrongly excluded.
        $academic = $subject !== '' || $major !== '' || $majorYear !== '';

        if ($department !== '' && ! $academic) {
            // Pure "list teachers in the X department" — match the home
            // department by full name OR short code ("CEIT").
            $query->whereHas('department', fn ($q) => $q
                ->where('name', 'like', "%{$department}%")
                ->orWhere('code', 'like', "%{$department}%"));
        }

        // Academic filters describe the SAME teaching assignment, so they are
        // constrained together inside a single whereHas — we must not match a
        // teacher who teaches English in some *other* year or program.
        if ($academic) {
            $query->whereHas('teacherAssignments.curriculumSubject', function ($cs) use ($subject, $major, $majorYear, $department) {
                if ($subject !== '') {
                    $cs->whereHas('subject', fn ($s) => $this->looseLike($s, ['name', 'code'], $subject));
                }

                if ($major !== '') {
                    $cs->whereHas('major', fn ($m) => $m
                        ->where('name', 'like', "%{$major}%")
                        ->orWhereHas('department', fn ($d) => $d
                            ->where('name', 'like', "%{$major}%")
                            ->orWhere('code', 'like', "%{$major}%")));
                }

                if ($department !== '') {
                    // The class's program department (e.g. "CEIT first year").
                    $cs->whereHas('major.department', fn ($d) => $d
                        ->where('name', 'like', "%{$department}%")
                        ->orWhere('code', 'like', "%{$department}%"));
                }

                if ($majorYear !== '') {
                    $cs->whereHas('majorYear', fn ($y) => ctype_digit($majorYear)
                        ? $y->where('year_number', (int) $majorYear)
                        : $y->where('name', 'like', "%{$majorYear}%"));
                }
            });
        }

        $total = (clone $query)->count();

        $items = $query
            ->with([
                'department:id,name',
                // The subjects a teacher teaches — this is the EVIDENCE the
                // answer layer needs so it can confidently say "X teaches
                // English" instead of hedging on a bare name+department list.
                'teacherAssignments.curriculumSubject.subject:id,code,name',
                'teacherAssignments.curriculumSubject.majorYear:id,name,year_number',
                'teacherAssignments.curriculumSubject.major:id,name',
            ])
            ->limit(10)
            ->get()
            ->map(fn (Teacher $t) => [
                'name' => $t->name,
                'department' => $t->department?->name,
                'position' => $t->position,
                'email' => $t->email,
                'image_url' => $t->image_url,
                'teaches' => $t->teacherAssignments
                    ->map(function ($assignment) {
                        $cs = $assignment->curriculumSubject;
                        if (! $cs || ! $cs->subject) {
                            return null;
                        }
                        $context = collect([$cs->majorYear?->name, $cs->major?->name])
                            ->filter()->implode(', ');

                        return $cs->subject->name.($context !== '' ? " ({$context})" : '');
                    })
                    ->filter()->unique()->values()->take(12)->all(),
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
