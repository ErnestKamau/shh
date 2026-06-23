<?php

namespace App\Services\Lab;

use App\AnalysisType;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AnalysisReferenceLabelResolver
{
    public function resolveToken(string $token): string
    {
        $token = trim($this->decryptIfNeeded($token));

        if ($token === '' || (! ctype_digit($token) && ! Str::isUuid($token))) {
            return $token;
        }

        $analyte = DB::table('analytes')->where('id', $token)->value('name');
        if (! empty($analyte)) {
            return (string) $analyte;
        }

        $analysisElementQuery = DB::table('analysis_elements')
            ->leftJoin('analytes', 'analytes.id', '=', 'analysis_elements.analyte_id');

        if (DB::connection()->getDriverName() === 'pgsql') {
            $analysisElementQuery->whereRaw('analysis_elements.id::text = ?', [$token]);
        } else {
            $analysisElementQuery->where('analysis_elements.id', $token);
        }

        $analysisElement = $analysisElementQuery
            ->select('analysis_elements.method as analysis_element_name', 'analytes.name as analyte_name')
            ->first();

        if ($analysisElement) {
            return (string) ($analysisElement->analyte_name ?: $analysisElement->analysis_element_name ?: $token);
        }

        $analysisType = AnalysisType::query()->find($token);

        return $analysisType?->name ?? $token;
    }

    /**
     * @return list<string>
     */
    public function extractTokens(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $tokens = [];

            foreach ($raw as $item) {
                $tokens = array_merge($tokens, $this->extractTokens($item));
            }

            return array_values(array_filter(array_map('trim', $tokens), fn (string $token): bool => $token !== ''));
        }

        $rawValue = trim($this->decryptIfNeeded((string) $raw));
        if ($rawValue === '') {
            return [];
        }

        if (str_starts_with($rawValue, '[')) {
            $decoded = json_decode($rawValue, true);
            if (is_array($decoded)) {
                return $this->extractTokens($decoded);
            }
        }

        if (str_contains($rawValue, ',')) {
            return array_values(array_filter(
                array_map('trim', explode(',', $rawValue)),
                fn (string $token): bool => $token !== ''
            ));
        }

        return [$rawValue];
    }

    public function resolveMixed(mixed $raw): string
    {
        $tokens = $this->extractTokens($raw);
        if ($tokens === []) {
            return '';
        }

        $labels = array_map(fn (string $token): string => $this->resolveToken($token), $tokens);

        return implode(', ', array_values(array_unique(array_filter($labels, fn (string $label): bool => $label !== ''))));
    }

    private function decryptIfNeeded(string $value): string
    {
        $value = trim($value);
        if ($value === '' || ! str_starts_with($value, 'eyJ')) {
            return $value;
        }

        try {
            return trim(Crypt::decryptString($value));
        } catch (\Throwable) {
            return $value;
        }
    }
}
