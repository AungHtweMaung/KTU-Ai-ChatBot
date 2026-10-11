<?php

namespace App\Services\Chat\Intents\Concerns;

use App\Models\Teacher;

/**
 * Matching for the free-form `teachers.position` column.
 *
 * WHY beyond a plain LIKE: positions are typed by hand in the admin panel, so
 * the stored wording rarely matches the word a user types. One record holds
 * "ractor, ractor of ktu"; a student asking "who is the rector?" — or asking
 * in Myanmar, which the NLU layer renders as the English "rector" — would get
 * "no information found" purely because of one letter.
 *
 * So: try the literal match first, and only when it finds nothing fall back to
 * the closest stored spelling. The fallback is deliberately narrow (a short
 * edit distance, and only for words long enough that a near-miss is meaningful)
 * so "lecturer" can never be answered with "demonstrator".
 */
trait PositionMatching
{
    use LooseMatching;

    /** Largest number of character edits still considered the same word. */
    private const MAX_EDITS = 2;

    /** Below this length a one-or-two letter difference is a different word. */
    private const MIN_FUZZY_LENGTH = 5;

    /**
     * Constrain the query to teachers whose position matches $position,
     * falling back to the nearest stored spelling when nothing matches.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Teacher>  $query
     */
    protected function wherePositionMatches($query, string $position): void
    {
        $this->looseLike($query, ['position'], $this->resolvePosition($position));
    }

    /**
     * The term to actually search for: the user's own word when it matches
     * something, otherwise the closest stored spelling (or the original word,
     * so an unmatched search still returns an honest empty result).
     */
    protected function resolvePosition(string $term): string
    {
        $needle = $this->squash($term);

        if ($needle === '') {
            return $term;
        }

        $stored = Teacher::query()
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->pluck('position');

        foreach ($stored as $stored_position) {
            if (str_contains($this->squash($stored_position), $needle)) {
                return $term;
            }
        }

        return $this->closestStoredWord($stored->all(), $needle) ?? $term;
    }

    /**
     * The stored word nearest to $needle within MAX_EDITS, or null when no
     * stored word is close enough to be the same role.
     *
     * Positions often pack several roles into one string ("Head of Department
     * / Professor"), so each word is compared separately.
     *
     * @param  list<string>  $positions
     */
    private function closestStoredWord(array $positions, string $needle): ?string
    {
        if (mb_strlen($needle) < self::MIN_FUZZY_LENGTH) {
            return null;
        }

        $best = null;
        $bestDistance = PHP_INT_MAX;

        foreach ($positions as $position) {
            $words = preg_split('/[^\p{L}]+/u', mb_strtolower($position), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($words as $word) {
                if (mb_strlen($word) < self::MIN_FUZZY_LENGTH) {
                    continue;
                }

                $distance = levenshtein($needle, $word);

                if ($distance < $bestDistance && $distance <= self::MAX_EDITS) {
                    $best = $word;
                    $bestDistance = $distance;
                }
            }
        }

        return $best;
    }
}
