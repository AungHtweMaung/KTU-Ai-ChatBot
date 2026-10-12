<?php

namespace Tests\Feature\Seeders;

use App\Models\Major;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Database\Seeders\AcademicDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards corrections applied on top of the university Google Sheet.
 *
 * The sheet sometimes lists a teacher against a curriculum they do not
 * actually teach; the seeder deliberately drops those, so a future
 * re-transcription of the sheet must not silently reintroduce them.
 */
class AcademicDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_ceit_teacher_is_not_assigned_to_the_civil_curriculum(): void
    {
        $this->seed(AcademicDataSeeder::class);

        $civil = Major::where('name', 'Civil Engineering')->firstOrFail();
        $teacher = Teacher::where('name', 'Dr. Khaing Myat Mon')->firstOrFail();

        $civilAssignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->whereHas('curriculumSubject', fn ($q) => $q->where('major_id', $civil->id))
            ->count();

        $this->assertSame(
            0,
            $civilAssignments,
            'Dr. Khaing Myat Mon (CEIT) must not be assigned to any Civil Engineering subject.',
        );
    }
}
