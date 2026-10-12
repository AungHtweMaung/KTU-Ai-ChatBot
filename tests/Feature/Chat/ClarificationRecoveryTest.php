<?php

namespace Tests\Feature\Chat;

use App\Models\Department;
use App\Models\Major;
use App\Services\Chat\ClarificationRecovery;
use App\Services\Chat\Contracts\AiProvider;
use App\Services\Chat\IntentRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "how many majors are there?" must never be answered with "could you clarify
 * which department you mean?" — the database can answer it outright.
 *
 * Phase 1 can return `clarification_required` for that question in two ways:
 * the model picks it, or its reply is unparseable JSON and the provider
 * substitutes it. Neither may end the turn any more.
 */
class ClarificationRecoveryTest extends TestCase
{
    use RefreshDatabase;

    /** Phase-1 stub returning a scripted sequence of envelopes. */
    private function fakeProvider(array ...$envelopes): void
    {
        $provider = new class($envelopes) implements AiProvider
        {
            public int $calls = 0;

            public function __construct(private array $envelopes) {}

            public function extractIntent(array $history, string $latestMessage): array
            {
                return $this->envelopes[min($this->calls++, count($this->envelopes) - 1)];
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

        $this->app->instance(AiProvider::class, $provider);
    }

    private static function clarification(): array
    {
        return [
            'intent' => 'clarification_required',
            'confidence' => 0.0,
            'filters' => [],
            'follow_up' => 'Could you please specify which department you mean?',
        ];
    }

    private function seedNineMajors(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            Major::create([
                'department_id' => Department::create([
                    'name' => "Department {$i}",
                    'code' => "D{$i}",
                ])->id,
                'name' => "Major {$i}",
            ]);
        }
    }

    /* ---------------------------------------------------------------
     |  Step 1 — the retry
     |--------------------------------------------------------------- */

    public function test_a_retry_rescues_a_one_off_clarification(): void
    {
        $this->seedNineMajors();

        $this->fakeProvider(
            self::clarification(),
            ['intent' => 'major_information', 'confidence' => 1.0, 'filters' => []],
        );

        $envelope = app(ClarificationRecovery::class)->recover(
            self::clarification(),
            [],
            'how many majors are there?',
        );

        $this->assertSame('major_information', $envelope['intent']);
    }

    /* ---------------------------------------------------------------
     |  Step 2 — the deterministic route
     |--------------------------------------------------------------- */

    public function test_deterministic_route_answers_a_bare_count_question(): void
    {
        $this->seedNineMajors();

        // Both model calls give up, so the deterministic route must take over.
        $this->fakeProvider(self::clarification());

        $envelope = app(ClarificationRecovery::class)->recover(
            self::clarification(),
            [],
            'how many majors are there?',
        );

        $this->assertSame('major_information', $envelope['intent']);
        $this->assertSame([], $envelope['filters']);

        $data = app(IntentRegistry::class)->dispatch($envelope['intent'], $envelope['filters']);

        $this->assertSame(9, $data['total_count']);
    }

    public function test_deterministic_route_handles_the_other_core_entities(): void
    {
        $router = app(ClarificationRecovery::class);

        $this->assertSame('major_information', $router->routeDeterministically('how many majors?')['intent']);
        $this->assertSame('department_information', $router->routeDeterministically('how many departments are there?')['intent']);
        $this->assertSame('teacher_search', $router->routeDeterministically('how many teachers?')['intent']);
        $this->assertSame('subject_search', $router->routeDeterministically('list of subjects')['intent']);
    }

    public function test_deterministic_route_declines_when_the_question_is_qualified(): void
    {
        $router = app(ClarificationRecovery::class);

        // A slice of the data — the route cannot build the filters, so it must
        // not answer with a wrong, unfiltered total.
        $this->assertNull($router->routeDeterministically('how many subjects in third year CEIT?'));
        $this->assertNull($router->routeDeterministically('how many teachers in civil?'));
        $this->assertNull($router->routeDeterministically('how many majors in year 2?'));
    }

    public function test_deterministic_route_declines_without_a_count_signal(): void
    {
        $router = app(ClarificationRecovery::class);

        $this->assertNull($router->routeDeterministically('what is a major?'));
        $this->assertNull($router->routeDeterministically('tell me about departments'));
    }

    public function test_deterministic_route_declines_when_two_entities_are_named(): void
    {
        $router = app(ClarificationRecovery::class);

        $this->assertNull($router->routeDeterministically('how many teachers and departments are there?'));
    }

    /* ---------------------------------------------------------------
     |  A genuinely ambiguous question still clarifies
     |--------------------------------------------------------------- */

    public function test_a_genuinely_ambiguous_question_still_asks_for_clarification(): void
    {
        $this->fakeProvider(self::clarification());

        $envelope = app(ClarificationRecovery::class)->recover(
            self::clarification(),
            [],
            'can you help me with that thing?',
        );

        $this->assertSame('clarification_required', $envelope['intent']);
    }

    /* ---------------------------------------------------------------
     |  A normal question is untouched (no extra model call)
     |--------------------------------------------------------------- */

    public function test_a_settled_intent_is_returned_unchanged(): void
    {
        $this->fakeProvider(
            ['intent' => 'greeting', 'confidence' => 1.0, 'filters' => []],
        );

        $envelope = ['intent' => 'major_information', 'confidence' => 1.0, 'filters' => []];

        $this->assertSame(
            $envelope,
            app(ClarificationRecovery::class)->recover($envelope, [], 'how many majors are there?'),
        );
    }
}
