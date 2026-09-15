<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Analyte;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Import quotation prep lines from Excel or PDF text.
 *
 * Commercial math (Amspec package):
 *   Total price = No. of samples × Unit price (ONCE per sample package)
 * quantity_required is free text (e.g. "Per Sample Swab"), not a multiplier.
 *
 * Expected Excel columns (flexible headers):
 *   Sample | Test | Method | quantity_required | quantity | unit_price | tax | pricing_mode
 * Legacy: sample_type | parameters (semicolon-separated)
 * Method selects the existing LIMS analysis element; it does not create methods.
 *
 * PDF uses pdftotext (poppler-utils); when missing, use Excel.
 */
class QuotationPrepImportService
{
    /** @var Collection<int, SampleType>|null */
    private ?Collection $sampleTypeCatalog = null;

    /** @var Collection<int, Analyte>|null */
    private ?Collection $analyteCatalog = null;

    public function __construct(
        private readonly QuotationPricingResolver $quotationPricingResolver,
        private readonly QuotationLabSectionScope $quotationLabSectionScope,
        private readonly PdfTextExtractor $pdfTextExtractor,
        private readonly AmspecPackagePdfParser $amspecPackagePdfParser,
        private readonly AmspecImportLabelMatcher $labelMatcher,
    ) {}

    /**
     * @return array{available: bool, binary: ?string, hint: string}
     */
    public function pdfImportCapability(): array
    {
        return $this->pdfTextExtractor->capability();
    }

    /**
     * @return array{created: int, rows: list<array<string, mixed>>, warnings: list<string>}
     */
    public function import(QuotationHeader $header, UploadedFile $file, string $format = 'excel', string $defaultPricingMode = 'per_package'): array
    {
        $format = strtolower(trim($format));
        if ($format === 'pdf' && ! $this->pdfTextExtractor->isAvailable()) {
            throw new RuntimeException($this->pdfTextExtractor->capability()['hint']);
        }

        $rawRows = match ($format) {
            'pdf' => $this->parsePdfRows($file),
            default => $this->parseExcelRows($file),
        };

        $defaultPricingMode = in_array($defaultPricingMode, [
            QuotationPricingResolver::PRICING_MODE_PER_PACKAGE,
            QuotationPricingResolver::PRICING_MODE_PER_TEST,
            QuotationPricingResolver::PRICING_MODE_AUTO,
        ], true) ? $defaultPricingMode : QuotationPricingResolver::PRICING_MODE_PER_PACKAGE;

        foreach ($rawRows as &$row) {
            if (empty($row['pricing_mode'])) {
                $row['pricing_mode'] = $defaultPricingMode;
            }
        }
        unset($row);

        return $this->persistRows($header, $rawRows);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseExcelRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);
        if ($matrix === []) {
            return [];
        }

        $headerIndex = $this->detectHeaderRow($matrix);
        $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $matrix[$headerIndex] ?? []);
        $rows = [];

        for ($i = $headerIndex + 1; $i < count($matrix); $i++) {
            $line = $matrix[$i];
            if (! is_array($line) || $this->rowIsEmpty($line)) {
                continue;
            }
            $mapped = $this->mapRow($headers, $line);
            if ($mapped !== null) {
                $rows[] = $mapped;
            }
        }

        return $this->collapseExcelPackageContinuations($rows);
    }

    /**
     * PDF text extract via pdftotext -layout when available.
     *
     * @return list<array<string, mixed>>
     */
    public function parsePdfRows(UploadedFile $file): array
    {
        return $this->amspecPackagePdfParser->parse(
            $this->pdfTextExtractor->extractLayoutText($file)
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rawRows
     * @return array{created: int, rows: list<array<string, mixed>>, warnings: list<string>}
     */
    public function persistRows(QuotationHeader $header, array $rawRows): array
    {
        $created = 0;
        $warnings = [];
        $persisted = [];

        foreach ($rawRows as $index => $raw) {
            $sampleTypeName = trim((string) ($raw['sample_type'] ?? ''));
            $sampleType = $this->resolveSampleType($sampleTypeName);
            if ($sampleType === null) {
                $warnings[] = 'Row '.($index + 1).': unknown sample type "'.$sampleTypeName.'"';

                continue;
            }

            $paramTokens = $this->splitParameters((string) ($raw['parameters'] ?? ''));
            $methodHints = $this->methodHintsForTokens($raw, $paramTokens);
            $elementIds = $this->resolveElementIdsForSampleType((string) $sampleType->id, $paramTokens, $methodHints);
            if ($paramTokens !== [] && $elementIds === []) {
                $warnings[] = 'Row '.($index + 1).': no matching parameters for "'.implode(', ', $paramTokens).'"';
            }

            $analysisTypeIds = AnalysisElements::query()
                ->whereIn('id', $elementIds)
                ->pluck('analysis_type_id')
                ->map(fn ($id) => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            try {
                $this->quotationLabSectionScope->assertAnalysisTypesAllowed($header, $analysisTypeIds);
                $this->quotationLabSectionScope->assertElementsAllowed($header, $elementIds);
            } catch (\RuntimeException $exception) {
                $warnings[] = 'Row '.($index + 1).': '.$exception->getMessage();

                continue;
            }

            $quantity = max(1, (int) ($raw['quantity'] ?? 1));
            $unitPrice = (float) ($raw['unit_price'] ?? 0);
            $pricingMode = (string) ($raw['pricing_mode'] ?? QuotationPricingResolver::PRICING_MODE_AUTO);
            $quantityRequired = trim((string) ($raw['quantity_required'] ?? ''));

            $normalizedRows = $this->quotationPricingResolver->normalizeManualDetailRows(
                $header,
                (string) $sampleType->id,
                implode(',', $analysisTypeIds),
                $elementIds,
                $quantity,
                $unitPrice,
                0.0,
                implode(',', $elementIds),
                '',
                '',
                '',
                $pricingMode,
                [],
                '',
                '',
                $quantityRequired,
            );

            foreach ($normalizedRows as $rowPayload) {
                $detail = new QuotationDetails();
                $detail->sample_type = $rowPayload['sample_type'];
                $detail->unit_price = $rowPayload['unit_price'];
                $detail->tax = $rowPayload['tax'];
                $detail->part_no = $rowPayload['part_no'];
                $detail->quantity = $rowPayload['quantity'];
                $detail->quantity_required = $rowPayload['quantity_required'] ?? null;
                $detail->accredited_analytes = $rowPayload['accredited_analytes'];
                $detail->subcontracted_analytes = $rowPayload['subcontracted_analytes'] ?? '';
                $detail->default_analytes = $rowPayload['default_analytes'];
                $detail->sub_acc_analytes = $rowPayload['sub_acc_analytes'];
                $detail->is_package = (bool) ($rowPayload['is_package'] ?? false);
                $detail->loq = (string) ($rowPayload['loq'] ?? '');
                $detail->mu_percent = (string) ($rowPayload['mu_percent'] ?? '');
                $detail->test_method = (string) ($rowPayload['test_method'] ?? '');
                $detail->tat = $rowPayload['tat'] ?? null;
                $detail->description = (string) ($rowPayload['description'] ?? '');
                $detail->quotation_header_id = $header->id;

                $analysisTypeIdsForDetail = array_filter(explode(',', (string) $rowPayload['part_no']));
                $this->quotationPricingResolver->persistInvoicableItemOnDetail(
                    $detail,
                    $analysisTypeIdsForDetail[0] ?? null
                );
                $detail->save();

                QuotationDetailAnalysisSplit::syncForDetail(
                    (string) $detail->id,
                    $analysisTypeIdsForDetail
                );

                $created++;
                $persisted[] = [
                    'id' => $detail->id,
                    'sample_type' => $sampleTypeName,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->unit_price,
                    'is_package' => (bool) $detail->is_package,
                ];
            }
        }

        return [
            'created' => $created,
            'rows' => $persisted,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  list<list<mixed>>  $matrix
     */
    private function detectHeaderRow(array $matrix): int
    {
        foreach ($matrix as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $joined = strtolower(implode(' ', array_map(fn ($c) => (string) $c, $row)));
            if (str_contains($joined, 'sample') || str_contains($joined, 'parameter') || str_contains($joined, 'unit price')) {
                return (int) $index;
            }
        }

        return 0;
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::of($header)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();

        return match (true) {
            str_contains($key, 'sample') && str_contains($key, 'type') => 'sample_type',
            $key === 'sample' || $key === 'sample_description' => 'sample_type',
            str_contains($key, 'method') => 'method',
            str_contains($key, 'parameter') || (str_contains($key, 'test') && ! str_contains($key, 'method')) => 'parameters',
            str_contains($key, 'quantity_required') || (str_contains($key, 'quantity') && str_contains($key, 'required')) => 'quantity_required',
            str_contains($key, 'no_of_sample') || $key === 'quantity' || $key === 'samples' => 'quantity',
            str_contains($key, 'unit_price') || $key === 'price' => 'unit_price',
            $key === 'tax' || str_contains($key, 'vat') => 'tax',
            str_contains($key, 'pricing_mode') || $key === 'mode' => 'pricing_mode',
            default => $key,
        };
    }

    /**
     * @param  list<string>  $headers
     * @param  list<mixed>  $line
     * @return array<string, mixed>|null
     */
    private function mapRow(array $headers, array $line): ?array
    {
        $mapped = [];
        foreach ($headers as $col => $key) {
            if ($key === '') {
                continue;
            }
            if (array_key_exists($key, $mapped) && trim((string) ($mapped[$key] ?? '')) !== '') {
                continue;
            }
            $mapped[$key] = $line[$col] ?? null;
        }

        if (trim((string) ($mapped['sample_type'] ?? '')) === '' && trim((string) ($mapped['parameters'] ?? '')) === '') {
            return null;
        }

        $mapped['unit_price'] = $this->cleanNumber($mapped['unit_price'] ?? 0);
        $mapped['quantity'] = (int) $this->cleanNumber($mapped['quantity'] ?? 1);
        if ((int) $mapped['quantity'] <= 0) {
            $mapped['quantity'] = 1;
        }

        return $mapped;
    }

    /**
     * Amspec Excel package layout: Sample / commercial columns on the first row;
     * follow-on rows only have Test (+ Method).
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function collapseExcelPackageContinuations(array $rows): array
    {
        $packages = [];
        /** @var array<string, mixed>|null $open */
        $open = null;

        foreach ($rows as $row) {
            $sample = trim((string) ($row['sample_type'] ?? ''));
            $params = trim((string) ($row['parameters'] ?? ''));
            $method = $this->rowMethodHint($row);
            $unitPrice = (float) ($row['unit_price'] ?? 0);

            if ($this->isExcelFooterLabel($sample) || $this->isExcelFooterLabel($params)) {
                if ($open !== null) {
                    $packages[] = $this->finalizeExcelPackage($open);
                    $open = null;
                }

                continue;
            }

            if ($sample !== '') {
                if ($open !== null && $this->sameImportSampleTypeLabel($sample, (string) ($open['sample_type'] ?? ''))) {
                    if ($params !== '') {
                        $open['_tests'][] = ['name' => $params, 'method' => $method];
                    }
                    $this->mergeExcelPackageCommercialFields($open, $row, $unitPrice);

                    continue;
                }

                if ($open !== null) {
                    $packages[] = $this->finalizeExcelPackage($open);
                }

                $open = $row;
                $open['sample_type'] = $sample;
                $open['_tests'] = $params !== '' ? [['name' => $params, 'method' => $method]] : [];
                $open['unit_price'] = $unitPrice;

                continue;
            }

            if ($params === '' || $open === null) {
                continue;
            }

            $open['_tests'][] = ['name' => $params, 'method' => $method];
            $this->mergeExcelPackageCommercialFields($open, $row, $unitPrice);
        }

        if ($open !== null) {
            $packages[] = $this->finalizeExcelPackage($open);
        }

        return $packages;
    }

    /**
     * @param  array<string, mixed>  $open
     * @return array<string, mixed>
     */
    private function finalizeExcelPackage(array $open): array
    {
        /** @var list<array{name: string, method: string}|string> $rawTests */
        $rawTests = is_array($open['_tests'] ?? null) ? $open['_tests'] : [];
        $names = [];
        $methods = [];

        foreach ($rawTests as $entry) {
            if (is_string($entry)) {
                $name = trim($entry);
                $method = '';
            } else {
                $name = trim((string) ($entry['name'] ?? ''));
                $method = trim((string) ($entry['method'] ?? ''));
            }

            if ($name === '' || is_numeric($name)) {
                continue;
            }

            $names[] = $name;
            $methods[] = $method;
        }
        unset($open['_tests']);

        $uniqueNames = [];
        $uniqueMethods = [];
        foreach ($names as $index => $name) {
            if (isset($uniqueNames[$name])) {
                if ($uniqueMethods[$name] === '' && ($methods[$index] ?? '') !== '') {
                    $uniqueMethods[$name] = $methods[$index];
                }

                continue;
            }
            $uniqueNames[$name] = true;
            $uniqueMethods[$name] = $methods[$index] ?? '';
        }

        $open['parameters'] = implode('; ', array_keys($uniqueNames));
        $open['method_hints'] = array_values($uniqueMethods);
        $open['pricing_mode'] = $open['pricing_mode'] ?? QuotationPricingResolver::PRICING_MODE_PER_PACKAGE;
        $open['is_package'] = true;

        return $open;
    }

    /**
     * @param  array<string, mixed>  $open
     * @param  array<string, mixed>  $row
     */
    private function mergeExcelPackageCommercialFields(array &$open, array $row, float $unitPrice): void
    {
        if ($unitPrice > 0 && (float) ($open['unit_price'] ?? 0) <= 0) {
            $open['unit_price'] = $unitPrice;
        }

        foreach (['quantity_required', 'quantity', 'tax', 'pricing_mode'] as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') {
                continue;
            }
            if (! array_key_exists($field, $open) || $open[$field] === null || $open[$field] === '') {
                $open[$field] = $row[$field];
            }
        }
    }

    private function isExcelFooterLabel(string $label): bool
    {
        $normalized = strtolower(trim($label));

        return in_array($normalized, [
            'net amount',
            'net',
            'subtotal',
            'sub total',
            'vat',
            'tax',
            'grand total',
            'total',
            'amount due',
        ], true);
    }

    private function sameImportSampleTypeLabel(string $left, string $right): bool
    {
        $left = trim($left);
        $right = trim($right);

        if ($left === '' || $right === '') {
            return false;
        }

        if (strcasecmp($left, $right) === 0) {
            return true;
        }

        return $this->labelMatcher->matches($left, $right);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowMethodHint(array $row): string
    {
        return trim((string) ($row['method'] ?? $row['test_method'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $paramTokens
     * @return list<string>
     */
    private function methodHintsForTokens(array $row, array $paramTokens): array
    {
        if (isset($row['method_hints']) && is_array($row['method_hints'])) {
            $hints = array_values(array_map(
                fn ($hint): string => trim((string) $hint),
                $row['method_hints']
            ));

            while (count($hints) < count($paramTokens)) {
                $hints[] = '';
            }

            return array_slice($hints, 0, count($paramTokens));
        }

        $single = $this->rowMethodHint($row);
        if ($single === '') {
            return array_fill(0, count($paramTokens), '');
        }

        $split = $this->splitParameters($single);
        if (count($split) === count($paramTokens)) {
            return $split;
        }

        if (count($paramTokens) === 1) {
            return [$single];
        }

        return array_fill(0, count($paramTokens), '');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseDelimitedTextLines(string $text): array
    {
        $lines = preg_split('/\R/', $text) ?: [];
        $rows = [];
        $headers = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/\s{2,}|\t/', $line) ?: [];
            $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
            if (count($parts) < 2) {
                continue;
            }

            if ($headers === null) {
                $maybe = array_map(fn ($h) => $this->normalizeHeader($h), $parts);
                if (in_array('sample_type', $maybe, true) || in_array('parameters', $maybe, true)) {
                    $headers = $maybe;

                    continue;
                }

                // Amspec-like: Sample | Test | Method | Qty Required | TAT | Samples | Unit | Total
                if (count($parts) >= 6) {
                    $rows[] = [
                        'sample_type' => $parts[0],
                        'parameters' => $parts[1],
                        'quantity_required' => $parts[3] ?? '',
                        'quantity' => (int) $this->cleanNumber($parts[5] ?? 1),
                        'unit_price' => $this->cleanNumber($parts[6] ?? 0),
                        'pricing_mode' => QuotationPricingResolver::PRICING_MODE_PER_PACKAGE,
                    ];
                }

                continue;
            }

            $mapped = $this->mapRow($headers, $parts);
            if ($mapped !== null) {
                $rows[] = $mapped;
            }
        }

        return $this->collapsePackageContinuations($rows);
    }

    /**
     * When PDF lists multiple tests under one sample package, merge into one commercial row.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function collapsePackageContinuations(array $rows): array
    {
        $collapsed = [];
        foreach ($rows as $row) {
            $sample = trim((string) ($row['sample_type'] ?? ''));
            $params = trim((string) ($row['parameters'] ?? ''));
            $unit = (float) ($row['unit_price'] ?? 0);

            $last = $collapsed[count($collapsed) - 1] ?? null;
            if ($last !== null
                && trim((string) ($last['sample_type'] ?? '')) === $sample
                && $sample !== ''
                && (float) ($last['unit_price'] ?? 0) === $unit
                && $unit > 0
            ) {
                $collapsed[count($collapsed) - 1]['parameters'] = trim(
                    (string) $last['parameters'].'; '.$params,
                    '; '
                );

                continue;
            }

            $collapsed[] = $row;
        }

        return $collapsed;
    }

    private function resolveSampleType(string $name): ?SampleType
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        foreach ($this->sampleTypeCatalog() as $sampleType) {
            if ($this->labelMatcher->matches($name, (string) ($sampleType->name ?? ''))
                || $this->labelMatcher->matches($name, (string) ($sampleType->code ?? ''))) {
                return $sampleType;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function splitParameters(string $raw): array
    {
        $parts = preg_split('/[;,|]+/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * @param  list<string>  $tokens
     * @param  list<string>  $methodHints
     * @return list<string>
     */
    private function resolveElementIdsForSampleType(string $sampleTypeId, array $tokens, array $methodHints = []): array
    {
        $ids = [];
        foreach ($tokens as $index => $token) {
            $analyte = $this->resolveAnalyte($token);
            if ($analyte === null) {
                continue;
            }

            $methodHint = trim((string) ($methodHints[$index] ?? ''));
            $query = AnalysisElements::query()
                ->with(['mmethod:id,name,code'])
                ->where('analyte_id', $analyte->id)
                ->whereHas('analysis_type', function ($q) use ($sampleTypeId) {
                    $q->where('sample_type_id', $sampleTypeId);
                })
                ->orderBy('id');

            /** @var Collection<int, AnalysisElements> $candidates */
            $candidates = $query->get();
            if ($candidates->isEmpty()) {
                continue;
            }

            $element = null;
            if ($methodHint === '') {
                $element = $candidates->first();
            } else {
                $element = $candidates->first(function (AnalysisElements $candidate) use ($methodHint): bool {
                    $code = trim((string) ($candidate->mmethod?->code ?? ''));
                    $name = trim((string) ($candidate->mmethod?->name ?? ''));

                    return ($code !== '' && $this->labelMatcher->methodCodesMatch($methodHint, $code))
                        || ($name !== '' && (
                            $this->labelMatcher->methodCodesMatch($methodHint, $name)
                            || $this->labelMatcher->matches($methodHint, $name)
                        ));
                });
            }

            if ($element !== null) {
                $ids[] = (string) $element->id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function resolveAnalyte(string $token): ?Analyte
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        foreach ($this->analyteCatalog() as $analyte) {
            if ($this->labelMatcher->matches($token, (string) ($analyte->name ?? ''))
                || $this->labelMatcher->matches($token, (string) ($analyte->code ?? ''))) {
                return $analyte;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, SampleType>
     */
    private function sampleTypeCatalog(): Collection
    {
        return $this->sampleTypeCatalog ??= SampleType::query()
            ->get(['id', 'name', 'code']);
    }

    /**
     * @return Collection<int, Analyte>
     */
    private function analyteCatalog(): Collection
    {
        return $this->analyteCatalog ??= Analyte::query()
            ->get(['id', 'name', 'code']);
    }

    /**
     * @param  list<mixed>  $line
     */
    private function rowIsEmpty(array $line): bool
    {
        foreach ($line as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function cleanNumber(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $str = str_replace([',', ' '], '', trim((string) $value));
        if (preg_match('/-?\d+(?:\.\d+)?/', $str, $m)) {
            return (float) $m[0];
        }

        return 0.0;
    }
}
