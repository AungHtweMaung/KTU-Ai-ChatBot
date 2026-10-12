<?php

namespace App\Services\Chat;

use App\Services\Chat\Contracts\AiProvider;
use Illuminate\Support\Facades\Log;

/**
 * Recovers from a dead-end `clarification_required`.
 *
 * WHY THIS EXISTS
 * ---------------
 * "how many majors are there?" is answerable straight from the database, yet
 * the bot sometimes replies "could you specify which department you mean?"
 * instead. That reply is produced by Phase 1: when intent extraction does not
 * settle on a data intent, no handler runs at all — so the majors table is
 * never queried and the user is asked a question the data could have answered.
 *
 * It reaches that state in two ways, and neither is recoverable in the old
 * code:
 *   1. The model itself picks `clarification_required`.
 *   2. The model's reply is not parseable JSON, so
 *      {@see \App\Services\Chat\Providers\AbstractAiProvider::fallbackIntent()}
 *      silently substitutes `clarification_required`.
 *
 * Both used to end the turn immediately, with nothing logged — an occasional
 * LLM hiccup was indistinguishable from a genuinely ambiguous question, and
 * the user just got asked to clarify. A single model call should not be able
 * to do that, so this class gives the pipeline two ways out:
 *
 *   1. Ask once more. The failure is usually a one-off (a truncated response,
 *      a rate-limited call), and a retry settles it.
 *   2. Fall back to a deterministic route for a bare "how many X are there?"
 *      question about one of the core entities — a shape the database can
 *      always answer with no filters at all.
 *
 * A genuinely ambiguous question still ends in a clarification; that intent
 * exists for a reason. It now simply has to survive both steps first.
 */
class ClarificationRecovery
{
    /**
     * Entities a bare count can be answered for, and the words that name them.
     * An empty filter set is exactly right here: the handler's total_count is
     * then the authoritative size of the whole set.
     *
     * @var array<string, list<string>>
     */
    private const ENTITY_INTENTS = [
        'major_information' => ['major', 'majors', 'မေဂျာ', 'အထူးပြုဘာသာ'],
        'department_information' => ['department', 'departments', 'ဌာန'],
        'teacher_search' => ['teacher', 'teachers', 'ဆရာ', 'ဆရာမ'],
        'subject_search' => ['subject', 'subjects', 'ဘာသာရပ်'],
    ];

    /** Wording that asks for a count or a whole list, not one record. */
    private const COUNT_SIGNALS = [
        'how many', 'number of', 'count of', 'list of', 'list all', 'all the',
        'ဘယ်နှစ်ခု', 'ဘယ်နှစ်ယောက်', 'ဘယ်နှ', 'စာရင်း',
    ];

    /**
     * A qualifier means the question is about a SLICE of the data (a year, a
     * department, a course code). The deterministic route cannot build those
     * filters, so such questions are left to the model rather than answered
     * with a wrong, unfiltered total.
     */
    private const QUALIFIER_WORDS = [
        'civil', 'ceit', 'electrical', 'electronic', 'mechanical',
        'mechatronic', 'metallurgy', 'nuclear', 'biotech', 'engineering',
        'first year', 'second year', 'third year', 'fourth year', 'fifth year',
    ];

    public function __construct(private readonly AiProvider $ai) {}

    /**
     * Replace a dead-end clarification with a real answer, when possible.
     *
     * @param  array{intent: string, confidence: float, filters: array<string, mixed>}  $envelope
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{intent: string, confidence: float, filters: array<string, mixed>, follow_up?: ?string}
     */
    public function recover(array $envelope, array $history, string $message): array
    {
        if (($envelope['intent'] ?? null) !== 'clarification_required') {
            return $envelope;
        }

        Log::warning('Intent extraction gave up; retrying once.', [
            'message' => $message,
            'confidence' => $envelope['confidence'] ?? null,
        ]);

        $retry = $this->ai->extractIntent($history, $message);

        if (($retry['intent'] ?? null) !== 'clarification_required') {
            return $retry;
        }

        $routed = $this->routeDeterministically($message);

        if ($routed !== null) {
            Log::warning('Retry also gave up; used the deterministic entity route.', [
                'message' => $message,
                'intent' => $routed['intent'],
            ]);

            return $routed;
        }

        return $envelope;
    }

    /**
     * A bare "how many majors are there?" style question, routed without the
     * model. Returns null whenever the question is not clearly of that shape.
     *
     * @return array{intent: string, confidence: float, filters: array<string, mixed>, follow_up: null}|null
     */
    public function routeDeterministically(string $message): ?array
    {
        $text = mb_strtolower($message);

        if ($this->hasQualifier($text) || ! $this->hasCountSignal($text)) {
            return null;
        }

        $matched = [];

        foreach (self::ENTITY_INTENTS as $intent => $words) {
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    $matched[] = $intent;
                    break;
                }
            }
        }

        // "how many teachers and departments" names two entities — the shape
        // is no longer unambiguous, so leave it to the model.
        if (count($matched) !== 1) {
            return null;
        }

        return [
            'intent' => $matched[0],
            'confidence' => 0.5,
            'filters' => [],
            'follow_up' => null,
        ];
    }

    private function hasCountSignal(string $text): bool
    {
        foreach (self::COUNT_SIGNALS as $signal) {
            if (str_contains($text, $signal)) {
                return true;
            }
        }

        return false;
    }

    private function hasQualifier(string $text): bool
    {
        // A digit means a year, semester or course code is present.
        if (preg_match('/\d/u', $text) === 1) {
            return true;
        }

        foreach (self::QUALIFIER_WORDS as $word) {
            if (str_contains($text, $word)) {
                return true;
            }
        }

        return false;
    }
}
