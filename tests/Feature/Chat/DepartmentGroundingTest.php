<?php

namespace Tests\Feature\Chat;

use App\Models\Department;
use App\Models\Teacher;
use App\Services\Chat\ChatService;
use App\Services\Chat\Contracts\AiProvider;
use App\Services\Chat\DepartmentGrounder;
use App\Services\Chat\FilterNormalizer;
use App\Services\Chat\Intents\TeacherSearchHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A department named in the LATEST message must win over the topic the model
 * carried over from earlier turns, and a filter it cannot translate must never
 * silently widen the query to every row.
 *
 * Both bugs produced the same visible symptom: asking about one department and
 * being shown another's staff — or the whole university's.
 */
class DepartmentGroundingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Phase-1 stub that replays a fixed envelope, and reports the row count it
     * was handed so the test can assert on what the database layer returned.
     */
    private function fakeProvider(array $envelope): void
    {
        $provider = new class($envelope) implements AiProvider
        {
            public function __construct(private array $envelope) {}

            public function extractIntent(array $history, string $latestMessage): array
            {
                return $this->envelope;
            }

            public function generateAnswer(string $userQuestion, string $intent, array $dbResult): string
            {
                return 'total='.($dbResult['total_count'] ?? '?');
            }

            public function name(): string
            {
                return 'fake';
            }
        };

        // instance() rather than bind(): AiProvider is registered as a
        // singleton, so a plain rebind could be ignored.
        $this->app->instance(AiProvider::class, $provider);
    }

    private function department(string $name, string $code): Department
    {
        return Department::create(['name' => $name, 'code' => $code]);
    }

    private function teacher(Department $department, string $name): Teacher
    {
        return Teacher::create([
            'department_id' => $department->id,
            'name' => $name,
            'email' => strtolower(preg_replace('/[^a-z0-9]+/i', '.', $name)).'@ktu.edu.mm',
            'position' => 'Lecturer',
        ]);
    }

    /** Civil has 18 teachers, CEIT has 12 — the numbers from the report. */
    private function seedTwoDepartments(): void
    {
        $civil = $this->department('Civil Engineering', 'Civil');
        $ceit = $this->department('Computer Engineering and Information Technology', 'CEIT');

        for ($i = 1; $i <= 18; $i++) {
            $this->teacher($civil, "Civil Teacher {$i}");
        }

        for ($i = 1; $i <= 12; $i++) {
            $this->teacher($ceit, "CEIT Teacher {$i}");
        }
    }

    /* ---------------------------------------------------------------
     |  The reported bug: "...Civil" then "ceit မေးရော ?"
     |--------------------------------------------------------------- */

    public function test_a_department_named_in_the_latest_message_overrides_the_carried_over_topic(): void
    {
        $this->seedTwoDepartments();

        // The model drifts: the previous turn was about Civil, and it left
        // Civil in the filters even though the user just asked about CEIT.
        $this->fakeProvider([
            'intent' => 'teacher_search',
            'confidence' => 1.0,
            'filters' => ['department' => 'Civil'],
        ]);

        $service = app(ChatService::class);

        $first = $service->send(['message' => 'civil ဌာနမှာ ဆရာ ဘယ်နှစ်ယောက်ရှိလဲ'], null);
        $this->assertSame('total=18', $first['assistant_message']['content']);

        $second = $service->send([
            'message' => 'ceit မေးရော ?',
            'conversation_id' => $first['conversation_id'],
            'guest_uuid' => $first['guest_uuid'],
        ], null);

        // Grounded on the latest message → CEIT's 12, never Civil's 18.
        $this->assertSame('total=12', $second['assistant_message']['content']);
    }

    public function test_grounder_replaces_a_carried_over_department(): void
    {
        $this->seedTwoDepartments();

        $grounded = app(DepartmentGrounder::class)->ground('ceit မေးရော ?', ['department' => 'Civil']);

        $this->assertSame('Computer Engineering and Information Technology', $grounded['department']);
    }

    public function test_grounder_leaves_filters_untouched_when_no_department_is_named(): void
    {
        $this->seedTwoDepartments();

        $filters = ['department' => 'Civil', 'major_year' => '2'];

        $this->assertSame($filters, app(DepartmentGrounder::class)->ground('who teaches English?', $filters));
    }

    public function test_grounder_ignores_subject_codes_that_merely_start_with_a_department_code(): void
    {
        $this->seedTwoDepartments();

        // "CEIT-4001" names a course, not the CEIT department.
        $grounder = app(DepartmentGrounder::class);

        $this->assertNull($grounder->departmentNamedIn('CEIT-4001 ကို ဘယ်သူသင်လဲ'));
        $this->assertNull($grounder->departmentNamedIn('who teaches CS 101?'));
    }

    public function test_grounder_requires_upper_case_for_short_codes_that_are_english_words(): void
    {
        $this->department('Metallurgy Engineering and Materials Science', 'Met');
        $this->department('Mechanical Engineering', 'ME');

        $grounder = app(DepartmentGrounder::class);

        // "met" / "me" as ordinary words must not hijack the question…
        $this->assertNull($grounder->departmentNamedIn('i met him yesterday'));
        $this->assertNull($grounder->departmentNamedIn('tell me about the library'));

        // …but the codes themselves still ground.
        $this->assertSame('Metallurgy Engineering and Materials Science', $grounder->departmentNamedIn('MET ဌာန'));
        $this->assertSame('Mechanical Engineering', $grounder->departmentNamedIn('ME ဌာန'));
    }

    /* ---------------------------------------------------------------
     |  Silent filter-drop: an unmatchable filter must not widen to all
     |--------------------------------------------------------------- */

    public function test_untranslatable_myanmar_filter_is_kept_so_the_query_does_not_widen_to_every_row(): void
    {
        $this->seedTwoDepartments();

        $filters = app(FilterNormalizer::class)->normalize(['department' => 'ဌာနမဟုတ်']);

        // Keeping the unmatchable value yields an honest empty result.
        $this->assertArrayHasKey('department', $filters);

        $data = app(TeacherSearchHandler::class)->handle($filters);

        // The old behaviour dropped the filter and listed all 30 teachers.
        $this->assertSame(0, $data['total_count'], 'An unmatchable filter must not degrade into "every row".');
    }

    public function test_mixed_script_filter_keeps_the_english_department_name(): void
    {
        $this->seedTwoDepartments();

        $normalizer = app(FilterNormalizer::class);

        $this->assertSame('CEIT', $normalizer->normalize(['department' => 'CEIT ဌာန'])['department']);

        $data = app(TeacherSearchHandler::class)
            ->handle($normalizer->normalize(['department' => 'CEIT ဌာန']));

        $this->assertSame(12, $data['total_count']);
    }
}
