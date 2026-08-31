<?php

namespace App\Services\Chat\Intents;

use App\Models\Subject;
use App\Services\Chat\Contracts\IntentHandler;
use App\Services\Chat\Intents\Concerns\LooseMatching;

/**
 * "Tell me about subject X" / "List CS subjects" / "What subjects do third
 * year CEIT students study?" — matches subjects by name/code, and can list a
 * class's curriculum when narrowed by major, department, year, or semester.
 */
class SubjectSearchHandler implements IntentHandler
{
    use LooseMatching;

    public function name(): string
    {
        return 'subject_search';
    }

    public function handle(array $filters): array
    {
        $subject = trim((string) ($filters['subject'] ?? ''));
        $major = trim((string) ($filters['major'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));
        $majorYear = trim((string) ($filters['major_year'] ?? ''));
        $semester = trim((string) ($filters['semester'] ?? ''));

        $query = Subject::query();

        if ($subject !== '') {
            // Loose match so "C++" finds "C ++", "cs101" finds "CS 101", etc.
            $query->where(fn ($q) => $this->looseLike($q, ['name', 'code'], $subject));
        }

        // Curriculum context — "subjects in third year CEIT" lists the subjects
        // that appear in a matching curriculum, via curriculum_subjects.
        if ($major !== '' || $department !== '' || $majorYear !== '' || $semester !== '') {
            $query->whereHas('curriculumSubjects', function ($cs) use ($major, $department, $majorYear, $semester) {
                if ($major !== '') {
                    $cs->whereHas('major', fn ($m) => $m->where('name', 'like', "%{$major}%"));
                }
                if ($department !== '') {
                    $cs->whereHas('major.department', fn ($d) => $d
                        ->where('name', 'like', "%{$department}%")
                        ->orWhere('code', 'like', "%{$department}%"));
                }
                if ($majorYear !== '') {
                    $cs->whereHas('majorYear', fn ($y) => ctype_digit($majorYear)
                        ? $y->where('year_number', (int) $majorYear)
                        : $y->where('name', 'like', "%{$majorYear}%"));
                }
                if ($semester !== '' && ctype_digit($semester)) {
                    $cs->where('semester', (int) $semester);
                }
            });
        }

        $total = (clone $query)->count();

        $items = $query
            ->limit(30)
            ->get()
            ->map(fn (Subject $s) => [
                'code' => $s->code,
                'name' => $s->name,
                'credits' => $s->credits,
                'description' => $s->description,
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
