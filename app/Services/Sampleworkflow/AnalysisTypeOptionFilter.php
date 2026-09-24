<?php

namespace App\Services\Sampleworkflow;

class AnalysisTypeOptionFilter
{
    /**
     * Keep analysis types that belong to the given sample type (optional name/code search).
     *
     * @param  list<array{id: string, name: string, code?: string, sample_type_id?: string|null}>  $analysisTypes
     * @return list<array{id: string, name: string, code?: string, sample_type_id?: string|null}>
     */
    public function forSampleType(array $analysisTypes, ?string $sampleTypeId, string $search = ''): array
    {
        $sampleTypeId = trim((string) $sampleTypeId);
        if ($sampleTypeId === '') {
            return [];
        }

        $types = array_values(array_filter($analysisTypes, function (array $type) use ($sampleTypeId) {
            return (string) ($type['sample_type_id'] ?? '') === $sampleTypeId;
        }));

        $search = trim($search);
        if ($search === '') {
            return $types;
        }

        return array_values(array_filter($types, function (array $type) use ($search) {
            return stripos((string) ($type['name'] ?? ''), $search) !== false
                || stripos((string) ($type['code'] ?? ''), $search) !== false;
        }));
    }
}
