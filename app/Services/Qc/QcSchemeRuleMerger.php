<?php

namespace App\Services\Qc;

use App\Models\QcModule\QcSchemeBinding;
use App\Models\QcModule\QcSchemeRule;
use Illuminate\Support\Collection;

class QcSchemeRuleMerger
{
    /**
     * Merge rule maps by binding mode.
     *
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $baseRules  rule_type => payload
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $overlayRules
     * @return array<string, array{value: string|null, scheme_id: string, mode: string, source: string}>
     */
    public function applyMode(string $mode, array $baseRules, array $overlayRules): array
    {
        return match ($mode) {
            QcSchemeBinding::MODE_OVERRIDE => $this->withSource($overlayRules !== [] ? $overlayRules : $baseRules, 'override'),
            QcSchemeBinding::MODE_MERGE => $this->merge($baseRules, $overlayRules),
            QcSchemeBinding::MODE_ADDITIVE => $this->additive($baseRules, $overlayRules),
            default => $this->withSource($overlayRules !== [] ? $overlayRules : $baseRules, 'override'),
        };
    }

    /**
     * Collect active rules for a list of scheme IDs into rule_type => payload.
     *
     * @param  array<int, string>  $schemeIds
     * @return array<string, array{value: string|null, scheme_id: string, mode: string}>
     */
    public function rulesForSchemes(array $schemeIds, string $mode = QcSchemeBinding::MODE_OVERRIDE): array
    {
        if ($schemeIds === []) {
            return [];
        }

        $rules = QcSchemeRule::query()
            ->whereIn('qc_scheme_id', $schemeIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $map = [];
        foreach ($rules as $rule) {
            // Later schemes in the list win when collecting from multiple schemes in one binding set
            $map[(string) $rule->rule_type] = [
                'value' => $rule->value !== null ? (string) $rule->value : null,
                'scheme_id' => (string) $rule->qc_scheme_id,
                'mode' => $mode,
            ];
        }

        return $map;
    }

    /**
     * @param  Collection<int, QcSchemeBinding>  $bindings
     * @return array<string, array{value: string|null, scheme_id: string, mode: string}>
     */
    public function rulesFromBindings(Collection $bindings): array
    {
        $schemeIds = $bindings
            ->pluck('qc_scheme_id')
            ->map(static fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $dominantMode = (string) ($bindings->first()?->mode ?? QcSchemeBinding::MODE_OVERRIDE);

        return $this->rulesForSchemes($schemeIds, $dominantMode);
    }

    /**
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $base
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $overlay
     * @return array<string, array{value: string|null, scheme_id: string, mode: string, source: string}>
     */
    private function merge(array $base, array $overlay): array
    {
        $result = $this->withSource($base, 'standard');

        foreach ($overlay as $type => $payload) {
            $result[$type] = array_merge($payload, ['source' => 'method_merge']);
        }

        return $result;
    }

    /**
     * Additive keeps base rules and adds overlay rule types that are not present;
     * for the same rule_type, method value is appended as type__extra when both exist.
     *
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $base
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $overlay
     * @return array<string, array{value: string|null, scheme_id: string, mode: string, source: string}>
     */
    private function additive(array $base, array $overlay): array
    {
        $result = $this->withSource($base, 'standard');

        foreach ($overlay as $type => $payload) {
            if (! isset($result[$type])) {
                $result[$type] = array_merge($payload, ['source' => 'method_additive']);
                continue;
            }

            // Same type already present: keep both via suffixed key so nothing is dropped.
            $result[$type.'__additive'] = array_merge($payload, ['source' => 'method_additive']);
        }

        return $result;
    }

    /**
     * @param  array<string, array{value: string|null, scheme_id: string, mode: string}>  $rules
     * @return array<string, array{value: string|null, scheme_id: string, mode: string, source: string}>
     */
    private function withSource(array $rules, string $source): array
    {
        $result = [];
        foreach ($rules as $type => $payload) {
            $result[$type] = array_merge($payload, ['source' => $source]);
        }

        return $result;
    }

    /**
     * @param  array<string, array{value: string|null, scheme_id?: string, mode?: string, source?: string}>  $rules
     */
    public function numericRuleValue(array $rules, string $ruleType): ?float
    {
        $raw = $rules[$ruleType]['value'] ?? null;
        if ($raw === null || $raw === '' || ! is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }
}
