<?php

namespace App\Services\Chat\Intents\Concerns;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * Case-, whitespace- and punctuation-insensitive "LIKE" matching for the
 * short identifiers users search on (teacher names, subject names/codes,
 * departments, majors, FAQ questions…).
 *
 * WHY: people almost never type an identifier exactly the way it is stored.
 * A teacher is saved as "Dr. Khaing Myat Mon" but asked about as
 * "Dr Khaing Myat Mon"; "C++" may be stored as "C ++"; someone writes
 * "cs101" for the code "CS 101". A plain `LIKE '%…%'` misses every one of
 * these — a stray period, space or capital letter is enough to produce a
 * false "no information found" even though the record exists.
 *
 * This trait normalises BOTH sides of the comparison — lower-cases and
 * removes every whitespace/punctuation character — so cosmetic differences
 * in spacing, capitalisation and punctuation can never cause a miss.
 *
 * The strip set is a fixed, explicit list (not a regex) and is used for both
 * the PHP term and the SQL column, so the two sides always line up and the
 * behaviour is identical on MySQL (production) and SQLite (test suite).
 *
 * The column list is always supplied by the handler (never user input), so
 * interpolating it into the expression is safe; the search term is bound.
 */
trait LooseMatching
{
    /**
     * Characters dropped from both the search term and the column before
     * comparison — only "cosmetic" separators, i.e. characters that never
     * carry meaning on their own in an identifier.
     *
     * Meaningful symbols are deliberately KEPT (`+`, `#`, `&`, `*`, `@`…), so
     * that "C++" stays "c++" instead of collapsing to "c" and matching every
     * subject that merely contains the letter c. `%` and `_` are stripped
     * because they are LIKE wildcards. Backslash is omitted: it would need
     * driver-specific escaping and never appears in the identifiers we match.
     *
     * @var list<string>
     */
    private const STRIP_CHARS = [
        ' ', "\t", "\n", "\r",
        '.', ',', ';', ':', '!', '?',
        "'", '’', '`', '"',
        '-', '_', '/',
        '(', ')', '[', ']', '{', '}',
        '%',
        '၊', '။', // Myanmar section / little-section marks
    ];

    /**
     * Normalise a value the same way {@see normalisedColumn()} normalises a
     * column: lower-case, then drop every character in STRIP_CHARS.
     */
    protected function squash(string $value): string
    {
        return str_replace(self::STRIP_CHARS, '', mb_strtolower(trim($value)));
    }

    /**
     * Add an OR-group of normalised, case/punctuation-insensitive LIKE
     * clauses across the given columns.
     *
     * @param  Builder  $query
     * @param  list<string>  $columns  trusted column names (never user input)
     */
    protected function looseLike($query, array $columns, string $term): void
    {
        $squashed = $this->squash($term);

        // A term that is nothing but punctuation/spacing carries no signal —
        // adding `LIKE '%%'` would match everything. Skip it instead.
        if ($squashed === '') {
            return;
        }

        $needle = '%'.$squashed.'%';

        $query->where(function ($q) use ($columns, $needle) {
            foreach ($columns as $column) {
                $q->orWhereRaw($this->normalisedColumn($column).' LIKE ?', [$needle]);
            }
        });
    }

    /**
     * SQL expression that reproduces {@see squash()} on a column: LOWER()
     * wrapped in one REPLACE() per strip character.
     *
     * Uses only LOWER/REPLACE so it runs unchanged on MySQL and SQLite.
     */
    private function normalisedColumn(string $column): string
    {
        $expression = "LOWER({$column})";

        foreach (self::STRIP_CHARS as $char) {
            $expression = "REPLACE({$expression}, ".$this->quoteSql($char).", '')";
        }

        return $expression;
    }

    /** Quote a single character as a SQL string literal. */
    private function quoteSql(string $char): string
    {
        return "'".str_replace("'", "''", $char)."'";
    }
}
