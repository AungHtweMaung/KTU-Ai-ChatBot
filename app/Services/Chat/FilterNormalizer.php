<?php

namespace App\Services\Chat;

/**
 * Translates business filter values into the language the database actually
 * stores (English).
 *
 * WHY THIS EXISTS
 * ---------------
 * Every entity name in the KTU database (departments, majors, subjects…) is
 * stored in English. When a user asks a question in Myanmar, the intent
 * extractor is instructed to emit English filter values — but LLMs, small
 * ones especially, do not do that reliably. Passing a Myanmar string into a
 * `LIKE '%…%'` clause can never match an English column, so the user would
 * silently get "no information found".
 *
 * This class is the deterministic safety net: it runs on every filter set
 * before it reaches an IntentHandler, so correctness no longer depends on the
 * model behaving. It is applied centrally in {@see IntentRegistry::dispatch()},
 * which means every handler benefits without any per-handler code.
 */
class FilterNormalizer
{
    /**
     * Canonical English term => Myanmar fragments that should resolve to it.
     *
     * Matching is substring-based and case-insensitive, so a fragment also
     * catches suffixed forms (e.g. "ဌာန" for "department", "မှာ" for "in").
     *
     * @var array<string, list<string>>
     */
    private const ALIASES = [
        // ---- Departments ----
        'Information Technology' => [
            'အင်းဖေမေးရှင်း',   // "information" (transliterated)
            'အချက်အလက်နည်းပညာ',
            'သတင်းအချက်အလက်',
            'အိုင်တီ',
        ],
        'Computer Science' => [
            'ကွန်ပျူတာသိပ္ပံ',
            'ကွန်ပြူတာသိပ္ပံ',
            'ကွန်ပျူတာ',
            'ကွန်ပြူတာ',
        ],
        'Civil' => [
            'မြို့ပြ',
            'ဆောက်လုပ်ရေး',
            'တည်ဆောက်ရေး',
        ],
        'Electrical Power' => [
            'လျှပ်စစ်စွမ်းအား',
            'လျှပ်စစ်ဓာတ်အား',
            'လျှပ်စစ်',
        ],
        'Electronic' => [
            'အီလက်ထရွန်နစ်',
            'အီလက်ထရောနစ်',
            'အီလက်ထရွန်း',
        ],
        'Mechanical' => [
            'စက်မှုအင်ဂျင်နီယာ',
            'စက်မှု',
        ],
        'BioTech' => [
            'ဇီဝနည်းပညာ',
            'ဘိုင်အိုတက်',
            'ဇီဝ',
        ],

        // ---- Subjects ----
        'Mathematics' => [
            'သင်္ချာ',
            'သင်္ချာဘာသာ',
        ],
        'English' => [
            'အင်္ဂလိပ်စာ',
            'အင်္ဂလိပ်',
        ],
        'Database' => [
            'ဒေတာဘေ့စ်',
            'ဒေတာဘေ့',
        ],
        'Statics' => [
            'စတက်တစ်',
        ],
    ];

    /**
     * Filter keys whose values name a database entity and therefore need
     * translating. Free-text keys (`query`, `announcement_keyword`) are left
     * alone — a Myanmar keyword search may legitimately match Myanmar content
     * stored in a description field.
     *
     * @var list<string>
     */
    private const TRANSLATABLE_KEYS = [
        'department',
        'major',
        'subject',
        'teacher',
        'fee_type',
        'registration_type',
    ];

    /**
     * Normalise an entire filter set.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function normalize(array $filters): array
    {
        foreach ($filters as $key => $value) {
            if (! is_string($value) || ! in_array($key, self::TRANSLATABLE_KEYS, true)) {
                continue;
            }

            $translated = $this->translate($value);

            if ($translated === null) {
                // Untranslatable Myanmar text can never match an English
                // column — keeping it guarantees zero rows. Dropping it
                // degrades gracefully to the unfiltered result, which the
                // answer layer can still present usefully.
                unset($filters[$key]);
                continue;
            }

            $filters[$key] = $translated;
        }

        return $filters;
    }

    /**
     * Translate a single value.
     *
     * @return string|null  English value, or null when the value is Myanmar
     *                      text with no known mapping.
     */
    private function translate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '' || ! $this->containsMyanmar($value)) {
            return $value; // already English (or empty) — leave untouched
        }

        foreach (self::ALIASES as $english => $fragments) {
            foreach ($fragments as $fragment) {
                if (str_contains($value, $fragment)) {
                    return $english;
                }
            }
        }

        return null;
    }

    /** Does the string contain any character in the Myanmar Unicode block? */
    private function containsMyanmar(string $value): bool
    {
        return (bool) preg_match('/[\x{1000}-\x{109F}]/u', $value);
    }
}
