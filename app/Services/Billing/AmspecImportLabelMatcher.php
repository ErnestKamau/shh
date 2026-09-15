<?php

namespace App\Services\Billing;

/**
 * Fuzzy label matching for AmSpec Excel/PDF import against LIMS sample types and analytes.
 *
 * Normalizes casing/spacing, strips trailing "*", drops "enumeration of" prefixes,
 * and applies light spelling aliases (moulds/molds, heterotopic/heterotropic).
 */
final class AmspecImportLabelMatcher
{
    /**
     * Whether an import label refers to the same LIMS catalogue label.
     *
     * Uses normalized key equality only (after stripping enumeration / @suffix / aliases).
     * Does not treat «Condensate Water» as a match for «Water».
     */
    public function matches(string $importLabel, string $limsLabel): bool
    {
        $importKeys = $this->matchKeys($importLabel);
        $limsKeys = $this->matchKeys($limsLabel);

        if ($importKeys === [] || $limsKeys === []) {
            return false;
        }

        foreach ($importKeys as $importKey) {
            foreach ($limsKeys as $limsKey) {
                if ($importKey === $limsKey) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function matchKeys(string $label): array
    {
        $base = $this->normalize($label);
        if ($base === '') {
            return [];
        }

        $keys = [$base];

        $withoutEnumeration = preg_replace('/^enumeration\s+of\s+(the\s+)?/', '', $base) ?? $base;
        $withoutEnumeration = trim($withoutEnumeration);
        if ($withoutEnumeration !== '' && $withoutEnumeration !== $base) {
            $keys[] = $withoutEnumeration;
        }

        // Drop trailing parenthetical / @condition noise: "electrical conductivity @25c"
        $withoutAt = preg_replace('/\s*@\s*.+$/', '', $base) ?? $base;
        $withoutAt = trim($withoutAt);
        if ($withoutAt !== '' && $withoutAt !== $base) {
            $keys[] = $withoutAt;
        }

        $aliased = [];
        foreach ($keys as $key) {
            $aliased[] = $key;
            $aliased[] = $this->applyAliases($key);
        }

        return array_values(array_unique(array_filter($aliased)));
    }

    public function normalize(string $label): string
    {
        $value = html_entity_decode($label, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = mb_strtolower(trim($value));
        $value = str_replace(['*', '×'], '', $value);
        $value = str_replace(['–', '—', '_'], ['-', '-', ' '], $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s*\((?:working\s+days|days)\)\s*/i', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * Whether an import method code refers to the same LIMS method
     * (AMS/C/SOP/054 ≡ AMS-C-SOP-054 ≡ AMS_C_SOP_054).
     */
    public function methodCodesMatch(string $importCode, string $limsCode): bool
    {
        $importKey = $this->normalizeMethodCode($importCode);
        $limsKey = $this->normalizeMethodCode($limsCode);

        return $importKey !== '' && $importKey === $limsKey;
    }

    public function normalizeMethodCode(string $code): string
    {
        $value = html_entity_decode($code, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = mb_strtolower(trim($value));

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }

    private function applyAliases(string $key): string
    {
        $replacements = [
            'moulds' => 'molds',
            'mould' => 'mold',
            'heterotopic' => 'heterotropic',
            'yeasts and moulds' => 'yeast and molds',
            'yeasts and molds' => 'yeast and molds',
            'yeast and moulds' => 'yeast and molds',
            'yeast and mould' => 'yeast and mold',
            'e coli' => 'e. coli',
            'e.coli' => 'e. coli',
        ];

        $out = $key;
        foreach ($replacements as $from => $to) {
            $out = str_replace($from, $to, $out);
        }

        return trim($out);
    }
}
