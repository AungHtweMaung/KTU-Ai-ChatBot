<?php

namespace Tests\Feature\Chat;

use App\Models\Department;
use App\Models\Faq;
use App\Models\Teacher;
use App\Services\Chat\Intents\DepartmentInformationHandler;
use App\Services\Chat\Intents\FaqSearchHandler;
use App\Services\Chat\Intents\TeacherProfileHandler;
use App\Services\Chat\Intents\TeacherSearchHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Search matching must be case-, whitespace- and punctuation-insensitive, so a
 * record is found however the user phrases the query — "Dr Khaing Myat Mon"
 * must find the teacher stored as "Dr. Khaing Myat Mon".
 */
class LooseMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(string $name, string $position = 'Lecturer'): Teacher
    {
        $department = Department::firstOrCreate(
            ['code' => 'CEIT'],
            ['name' => 'Computer Engineering and Information Technology'],
        );

        return Teacher::create([
            'department_id' => $department->id,
            'name' => $name,
            'email' => strtolower(preg_replace('/[^a-z]+/i', '.', $name)).'@ktu.edu.mm',
            'position' => $position,
        ]);
    }

    public function test_teacher_search_ignores_case_spacing_and_punctuation(): void
    {
        $this->teacher('Dr. Khaing Myat Mon', 'Head of Department / Professor');

        $handler = app(TeacherSearchHandler::class);

        $queries = [
            'Dr Khaing Myat Mon',   // missing the period
            'dr khaing myat mon',   // all lower-case
            'DR. KHAING MYAT MON',  // all upper-case
            'Khaing Myat Mon',      // name only
        ];

        foreach ($queries as $query) {
            $result = $handler->handle(['teacher' => $query]);

            $this->assertSame(1, $result['total_count'], "Teacher not found for query: {$query}");
            $this->assertSame('Dr. Khaing Myat Mon', $result['items'][0]['name']);
        }
    }

    public function test_teacher_profile_ignores_punctuation_and_case(): void
    {
        $this->teacher('Dr. Khaing Myat Mon');

        $profile = app(TeacherProfileHandler::class)->handle(['teacher' => 'dr khaing myat mon']);

        $this->assertSame('Dr. Khaing Myat Mon', $profile['name'] ?? null);
    }

    public function test_teacher_search_still_returns_nothing_for_a_non_match(): void
    {
        $this->teacher('Dr. Khaing Myat Mon');

        $result = app(TeacherSearchHandler::class)->handle(['teacher' => 'zzz-not-a-real-name']);

        $this->assertSame(0, $result['total_count']);
    }

    public function test_department_code_match_is_case_insensitive(): void
    {
        Department::create(['name' => 'Computer Engineering and Information Technology', 'code' => 'CEIT']);

        $handler = app(DepartmentInformationHandler::class);

        $this->assertSame(1, $handler->handle(['department' => 'ceit'])['total_count']);
        $this->assertSame(1, $handler->handle(['department' => 'CEIT'])['total_count']);
    }

    public function test_faq_search_is_case_and_space_insensitive(): void
    {
        Faq::create([
            'category' => 'Hostel',
            'question' => 'How do I apply for a hostel room?',
            'answer' => 'Contact the student affairs office.',
            'keywords' => 'hostel, dormitory, အဆောင်',
            'is_published' => true,
        ]);

        $handler = app(FaqSearchHandler::class);

        $this->assertSame(1, $handler->handle(['query' => 'HOSTEL'])['total_count']);
        $this->assertSame(1, $handler->handle(['query' => 'hostel  room'])['total_count']);
        $this->assertSame(1, $handler->handle(['query' => 'DORMITORY'])['total_count']);
        $this->assertSame(0, $handler->handle(['query' => 'library'])['total_count']);
    }
}
