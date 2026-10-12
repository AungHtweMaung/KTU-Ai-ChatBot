<?php

namespace App\Services\Chat;

use App\Models\Department;

/**
 * Deterministic grounding of an explicitly-named department in the user's
 * LATEST message.
 *
 * WHY THIS EXISTS
 * ---------------
 * Phase 1 (intent extraction) sees the recent conversation as context, which
 * is what lets a bare follow-up like "what about CEIT?" resolve at all. The
 * cost is that the model may carry the PREVIOUS topic's department forward
 * instead of replacing it — ask about Civil, then ask "ceit မေးရော ?", and
 * the reply can come back with Civil's staff all over again. The model is the
 * only thing standing between the user and that mix-up, and models drift.
 *
 * This class is the deterministic safety net for that specific failure: if
 * the latest message names a KTU department outright, that department is the
 * one the user is asking about, full stop. Whatever the conversation was
 * about before is irrelevant.
 *
 * Deliberately narrow, because a false positive is as bad as a miss:
 *  - It only grounds on department CODES ("CEIT", "Civil"), never on full
 *    names. Full names are long and unambiguous, so the NLU already handles
 *    them; codes are exactly what a small model fumbles.
 *  - Codes of 3 characters or fewer ("EP", "ME", "Met", "EM") must appear in
 *    UPPER CASE. In mixed case they collide with ordinary English words —
 *    "met", "me", "em", "ep" — and grounding on those would hijack unrelated
 *    questions.
 *  - Subject codes ("CEIT-4001", "CS 101") are masked out first. They contain
 *    a department prefix but name a *course*, and treating them as a
 *    department would filter the query down to nothing.
 */
class DepartmentGrounder
{
    /**
     * Codes at or below this length must be upper-case to count, so that
     * "met"/"me"/"em" as ordinary words never trigger grounding.
     */
    private const CASE_SENSITIVE_CODE_LENGTH = 3;

    /** A course code such as "CEIT-4001" / "CS 101" — not a department. */
    private const SUBJECT_CODE_PATTERN = '/\b[A-Za-z]{2,6}\s*-?\s*\d{3,}\b/u';

    /** @var array<string, string>|null  lower-cased code => canonical name */
    private ?array $codes = null;

    /**
     * Force `filters.department` to the department the latest message names,
     * when it names one.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function ground(string $message, array $filters): array
    {
        $name = $this->departmentNamedIn($message);

        if ($name === null) {
            return $filters;
        }

        // The latest message wins: overwrite whatever the model carried over
        // from earlier turns.
        $filters['department'] = $name;

        return $filters;
    }

    /**
     * Canonical department name named in the message, or null.
     */
    public function departmentNamedIn(string $message): ?string
    {
        $codes = $this->codes();

        if ($codes === []) {
            return null;
        }

        // Strip course codes so their department prefix is not mistaken for a
        // department mention ("CEIT-4001 ကို ဘယ်သူသင်လဲ" is about a course).
        $haystack = preg_replace(self::SUBJECT_CODE_PATTERN, ' ', $message) ?? $message;

        // Split on anything that is not a letter or digit, so "ceit?" and
        // "(CEIT)" and "ceit ဌာန" all yield the bare token "ceit".
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $haystack, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($tokens as $token) {
            $lower = mb_strtolower($token);
            $name = $codes[$lower] ?? null;

            if ($name === null) {
                continue;
            }

            if (mb_strlen($token) <= self::CASE_SENSITIVE_CODE_LENGTH && $token !== mb_strtoupper($token)) {
                continue;
            }

            return $name;
        }

        return null;
    }

    /**
     * Department code (lower-cased) => canonical department name.
     *
     * Cached per request — the chat pipeline grounds once per turn.
     *
     * @return array<string, string>
     */
    private function codes(): array
    {
        if ($this->codes !== null) {
            return $this->codes;
        }

        return $this->codes = Department::query()
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->pluck('name', 'code')
            ->mapWithKeys(fn (string $name, string $code) => [mb_strtolower($code) => $name])
            ->all();
    }
}
