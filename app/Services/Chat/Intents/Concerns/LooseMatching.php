<?php

namespace App\Services\Chat\Intents\Concerns;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * Whitespace- and case-insensitive "LIKE" matching for short identifiers
 * such as subject names and codes.
 *
 * WHY: Users type "C++" but the record may be stored as "C ++"; someone
 * writes "cs101" but the code is "CS 101". A plain `LIKE '%C++%'` misses
 * "C ++" purely because of a stray space. This normalises BOTH sides by
 * lower-casing and stripping spaces before comparing, so cosmetic spacing
 * and capitalisation never cause a false "no results".
 *
 * The column list is always supplied by the handler (never user input), so
 * interpolating it into whereRaw is safe; the search term is bound.
 */
trait LooseMatching
{
    /** Lower-case and remove all spaces from a search term. */
    protected function squash(string $value): string
    {
        return str_replace(' ', '', mb_strtolower(trim($value)));
    }

    /**
     * Add an OR-group of whitespace/case-insensitive LIKE clauses across the
     * given columns. Uses REPLACE(LOWER(col), ' ', '') so it works on both
     * MySQL and SQLite.
     *
     * @param  Builder  $query
     * @param  list<string>  $columns
     */
    protected function looseLike($query, array $columns, string $term): void
    {
        $needle = '%'.$this->squash($term).'%';

        $query->where(function ($q) use ($columns, $needle) {
            foreach ($columns as $column) {
                $q->orWhereRaw("REPLACE(LOWER($column), ' ', '') LIKE ?", [$needle]);
            }
        });
    }
}
