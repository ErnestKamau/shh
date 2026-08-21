<?php

namespace App\Services\Billing;

/**
 * Parse Amspec UAE “Quotation - preparation” style PDF text (pdftotext -layout).
 *
 * Layout:
 *   Sample | Test | Method | Quantity Required | TAT | No. Of Samples | Unit Price | Total
 * Commercial columns merge across tests in a sample package.
 * Total = No. of samples × Unit price (ONCE) — never × number of tests.
 */
class AmspecPackagePdfParser
{
    /**
     * @return list<array{
     *     sample_type: string,
     *     parameters: string,
     *     quantity_required: string,
     *     quantity: int,
     *     unit_price: float,
     *     tat: string,
     *     pricing_mode: string,
     *     is_package: bool
     * }>
     */
    public function parse(string $text): array
    {
        $lines = preg_split('/\R/', $text) ?: [];
        $packages = [];
        /** @var list<string> $orphanTests */
        $orphanTests = [];
        /** @var array<string, mixed>|null $open */
        $open = null;

        foreach ($lines as $rawLine) {
            $line = rtrim($rawLine);
            if (trim($line) === '') {
                continue;
            }
            if ($this->isHeaderLine($line) || $this->isNoiseLine($line)) {
                continue;
            }

            $parts = preg_split('/\s{2,}/', trim($line)) ?: [];
            $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
            if ($parts === []) {
                continue;
            }

            $commercial = $this->extractCommercialTail($parts);
            $lead = $commercial['lead'] ?? $parts;
            [$sample, $test] = $this->splitSampleAndTest($lead, $commercial !== null);

            if ($test !== '' && $this->looksLikeSectionBanner($test) && $commercial === null && $sample === '') {
                continue;
            }

            if ($commercial !== null) {
                // Close previous open package.
                if ($open !== null) {
                    $packages[] = $this->toRow($open);
                }

                $tests = $orphanTests;
                $orphanTests = [];
                if ($test !== '') {
                    $tests[] = $test;
                }

                $open = [
                    'sample_type' => $sample !== '' ? $sample : (string) ($open['sample_type'] ?? ''),
                    'tests' => $tests,
                    'quantity_required' => $commercial['quantity_required'],
                    'quantity' => $commercial['quantity'],
                    'unit_price' => $commercial['unit_price'],
                    'tat' => $commercial['tat'],
                ];

                if ($open['sample_type'] === '' && $sample !== '') {
                    $open['sample_type'] = $sample;
                }

                continue;
            }

            // Test-only (or sample without commercial — rare): attach to open package or orphan.
            if ($test === '' && $sample === '') {
                continue;
            }

            $label = $test !== '' ? $test : $sample;
            if ($test === '' && $sample !== '' && ! $this->looksLikeTestName($sample)) {
                continue;
            }

            // Micro packages (AIR / Swab) should not absorb chemical water parameters that
            // belong to the next sample block in the Amspec template.
            if ($open !== null
                && $this->isMicroSampleName((string) $open['sample_type'])
                && $this->looksLikeChemicalTest($label)
            ) {
                $packages[] = $this->toRow($open);
                $open = null;
            }

            if ($open !== null) {
                $open['tests'][] = $label;
            } else {
                $orphanTests[] = $label;
            }
        }

        if ($open !== null) {
            $packages[] = $this->toRow($open);
        }

        return array_values(array_filter(
            $packages,
            fn (array $row): bool => trim((string) $row['sample_type']) !== ''
                || trim((string) $row['parameters']) !== ''
        ));
    }

    private function peelTrailingTestFromSample(string $token): array
    {
        if (preg_match('/^(.*?)\s+(Magnesium|Calcium|Sodium|Chloride|pH|Iron|Lead|Copper|Sulphate|Sulfate|Nitrite|Phosphate|Bromate|Appearance|Taste|Odour|Colour|Turbidity)$/i', $token, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return ['', ''];
    }

    /**
     * @param  array{sample_type: string, tests: list<string>, quantity_required: string, quantity: int, unit_price: float, tat: string}  $open
     * @return array{sample_type: string, parameters: string, quantity_required: string, quantity: int, unit_price: float, tat: string, pricing_mode: string, is_package: bool}
     */
    private function toRow(array $open): array
    {
        $tests = array_values(array_unique(array_filter(array_map('trim', $open['tests']), function (string $t): bool {
            if ($t === '' || is_numeric($t)) {
                return false;
            }
            // Drop footer fragments that slipped into the parameter list.
            if (preg_match('/^\d+(\.\d+)?$/', $t)) {
                return false;
            }

            return true;
        })));

        return [
            'sample_type' => (string) $open['sample_type'],
            'parameters' => implode('; ', $tests),
            'quantity_required' => (string) $open['quantity_required'],
            'quantity' => max(1, (int) $open['quantity']),
            'unit_price' => (float) $open['unit_price'],
            'tat' => (string) $open['tat'],
            'pricing_mode' => QuotationPricingResolver::PRICING_MODE_PER_PACKAGE,
            'is_package' => true,
        ];
    }

    /**
     * @param  list<string>  $lead
     * @return array{0: string, 1: string} sample, test
     */
    private function splitSampleAndTest(array $lead, bool $hasCommercial): array
    {
        $tokens = [];
        foreach ($lead as $token) {
            if ($this->looksLikeMethod($token)) {
                continue;
            }
            $tokens[] = $token;
        }

        if ($tokens === []) {
            return ['', ''];
        }

        if (count($tokens) === 1) {
            $only = $tokens[0];
            [$splitSample, $splitTest] = $this->peelTrailingTestFromSample($only);
            if ($splitSample !== '' && $splitTest !== '') {
                return [$splitSample, $splitTest];
            }
            if ($hasCommercial && ! $this->looksLikeTestName($only)) {
                return [$only, ''];
            }
            if ($this->looksLikeTestName($only)) {
                return ['', $only];
            }

            return [$hasCommercial ? $only : '', $hasCommercial ? '' : $only];
        }

        // First token = sample, remainder = test (Amspec often glues "Condensate Water" + "Magnesium").
        $sample = array_shift($tokens);
        $test = trim(implode(' ', $tokens));
        if ($this->looksLikeTestName((string) $sample) && ! $hasCommercial) {
            return ['', trim($sample.' '.$test)];
        }

        return [(string) $sample, $test];
    }

    private function looksLikeTestName(string $token): bool
    {
        $lower = strtolower($token);

        return str_contains($lower, 'enumeration')
            || str_contains($lower, 'plate count')
            || str_contains($lower, 'yeasts')
            || str_contains($lower, 'mould')
            || str_contains($lower, 'conductivity')
            || str_contains($lower, 'suspended solids')
            || str_contains($lower, 'dissolved solids')
            || str_contains($lower, 'alkalinity')
            || str_contains($lower, 'legionella')
            || str_contains($lower, 'heterotopic')
            || str_contains($lower, 'chlorine')
            || str_contains($lower, 'turbidity')
            || (bool) preg_match('/^(ph|chloride|sodium|calcium|magnesium|sulphate|sulfate|nitrite|phosphate|bromate|iron|lead|copper|appearance|taste|odour|odor|colour|color)\b/i', $token);
    }

    private function isMicroSampleName(string $sample): bool
    {
        $lower = strtolower(trim($sample));

        return $lower === 'air'
            || str_contains($lower, 'swab')
            || str_contains($lower, 'plate');
    }

    private function looksLikeChemicalTest(string $token): bool
    {
        $lower = strtolower($token);

        return str_contains($lower, 'conductivity')
            || str_contains($lower, 'suspended solids')
            || str_contains($lower, 'dissolved solids')
            || str_contains($lower, 'alkalinity')
            || str_contains($lower, 'chloride')
            || str_contains($lower, 'sodium')
            || str_contains($lower, 'calcium')
            || str_contains($lower, 'magnesium')
            || str_contains($lower, 'sulphate')
            || str_contains($lower, 'sulfate')
            || str_contains($lower, 'nitrite')
            || str_contains($lower, 'phosphate')
            || str_contains($lower, 'bromate')
            || (bool) preg_match('/^(ph|iron|lead|copper)\b/i', $token);
    }

    private function isHeaderLine(string $line): bool
    {
        $lower = strtolower($line);

        return str_contains($lower, 'sample')
            && (str_contains($lower, 'unit price') || str_contains($lower, 'quantity required'));
    }

    private function isNoiseLine(string $line): bool
    {
        $lower = strtolower(trim($line));

        return str_starts_with($lower, 'page ')
            || str_contains($lower, 'net amount')
            || str_contains($lower, 'vat amount')
            || str_contains($lower, 'total amount')
            || str_contains($lower, 'terms and conditions')
            || str_contains($lower, 'laboratory ref')
            || str_contains($lower, 'dear sir');
    }

    private function looksLikeMethod(string $token): bool
    {
        return (bool) preg_match('/^AMS\//i', $token)
            || (bool) preg_match('/SOP\/\d+/i', $token);
    }

    private function looksLikeSectionBanner(string $token): bool
    {
        $t = strtolower(trim($token));

        return in_array($t, [
            'micro analysis',
            'chemical analysis',
            'microbiological analysis',
            'physical analysis',
        ], true);
    }

    /**
     * @param  list<string>  $parts
     * @return array{lead: list<string>, quantity_required: string, tat: string, quantity: int, unit_price: float, total: float}|null
     */
    private function extractCommercialTail(array $parts): ?array
    {
        $n = count($parts);
        if ($n < 4) {
            return null;
        }

        $total = $parts[$n - 1];
        $unit = $parts[$n - 2];
        $samples = $parts[$n - 3];
        $tat = $parts[$n - 4];

        if (! is_numeric($total) || ! is_numeric($unit) || ! is_numeric($samples) || ! is_numeric($tat)) {
            return null;
        }

        $leadEnd = $n - 4;
        $qtyRequiredParts = [];
        while ($leadEnd > 0) {
            $candidate = $parts[$leadEnd - 1];
            if ($this->looksLikeMethod($candidate) || is_numeric($candidate)) {
                break;
            }
            if (preg_match('/^per\b/i', $candidate)) {
                array_unshift($qtyRequiredParts, $candidate);
                $leadEnd--;

                continue;
            }
            break;
        }

        return [
            'lead' => array_slice($parts, 0, $leadEnd),
            'quantity_required' => trim(implode(' ', $qtyRequiredParts)),
            'tat' => (string) (int) $tat,
            'quantity' => max(1, (int) $samples),
            'unit_price' => (float) $unit,
            'total' => (float) $total,
        ];
    }
}
