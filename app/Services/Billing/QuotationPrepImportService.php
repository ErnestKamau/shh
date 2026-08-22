<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use Illuminate\Http\UploadedFile;
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
 *   sample_type | parameters | quantity_required | quantity | unit_price | tax | pricing_mode
 * parameters = comma/semicolon separated analyte or element names.
 *
 * PDF uses pdftotext (poppler-utils); when missing, use Excel.
 */
class QuotationPrepImportService
{
    public function __construct(
        private readonly QuotationPricingResolver $quotationPricingResolver,
        private readonly QuotationLabSectionScope $quotationLabSectionScope,
        private readonly PdfTextExtractor $pdfTextExtractor,
        private readonly AmspecPackagePdfParser $amspecPackagePdfParser,
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

        return $rows;
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
            $elementIds = $this->resolveElementIdsForSampleType((string) $sampleType->id, $paramTokens);
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
            str_contains($key, 'parameter') || str_contains($key, 'test') && ! str_contains($key, 'method') => 'parameters',
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

        return SampleType::query()
            ->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(name) = ?', [strtolower($name)])
                    ->orWhereRaw('LOWER(code) = ?', [strtolower($name)]);
            })
            ->first();
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
     * @return list<string>
     */
    private function resolveElementIdsForSampleType(string $sampleTypeId, array $tokens): array
    {
        $ids = [];
        foreach ($tokens as $token) {
            $element = AnalysisElements::query()
                ->where(function ($q) use ($token) {
                    $q->whereRaw('LOWER(parametername) = ?', [strtolower($token)])
                        ->orWhereHas('analyte', function ($aq) use ($token) {
                            $aq->whereRaw('LOWER(name) = ?', [strtolower($token)]);
                        });
                })
                ->first();

            if ($element !== null) {
                $ids[] = (string) $element->id;
            }
        }

        return array_values(array_unique($ids));
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
