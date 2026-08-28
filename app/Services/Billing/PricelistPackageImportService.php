<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Analyte;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\SampleType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Import pricelist items (per-package or per-test) from Excel or PDF text.
 *
 * Package math documentation for agents:
 *   When is_package / pricing_mode=per_package:
 *     quotation Total = No. of samples × this unit price (ONCE)
 *   Per-test items bill separately per parameter.
 *
 * Expected Excel columns (flexible headers):
 *   sample_type | parameters | cost_price | selling_price | tax | pricing_mode
 * Legacy unit_price maps to selling_price. cost_price defaults to 0 when omitted.
 *
 * PDF uses pdftotext (poppler-utils); when missing, use Excel.
 */
class PricelistPackageImportService
{
    /** @var Collection<int, SampleType>|null */
    private ?Collection $sampleTypeCatalog = null;

    /** @var Collection<int, Analyte>|null */
    private ?Collection $analyteCatalog = null;

    public function __construct(
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
     * @return array{created: int, updated: int, warnings: list<string>, items: list<array<string, mixed>>}
     */
    public function import(
        Pricelist $pricelist,
        UploadedFile $file,
        string $format = 'excel',
        string $defaultPricingMode = 'per_package',
    ): array {
        $format = strtolower(trim($format));
        if ($format === 'pdf' && ! $this->pdfTextExtractor->isAvailable()) {
            throw new RuntimeException($this->pdfTextExtractor->capability()['hint']);
        }

        $rows = match ($format) {
            'pdf' => $this->parsePdfRows($file),
            default => $this->parseExcelRows($file),
        };

        return $this->persistRows($pricelist, $rows, $defaultPricingMode);
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

        $headerIndex = 0;
        foreach ($matrix as $index => $row) {
            $joined = strtolower(implode(' ', array_map(fn ($c) => (string) $c, is_array($row) ? $row : [])));
            if (str_contains($joined, 'sample') || str_contains($joined, 'parameter') || str_contains($joined, 'price')) {
                $headerIndex = (int) $index;
                break;
            }
        }

        $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $matrix[$headerIndex] ?? []);
        $rows = [];
        for ($i = $headerIndex + 1; $i < count($matrix); $i++) {
            $line = $matrix[$i];
            if (! is_array($line)) {
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
     * @return list<array<string, mixed>>
     */
    public function parsePdfRows(UploadedFile $file): array
    {
        return $this->amspecPackagePdfParser->parse(
            $this->pdfTextExtractor->extractLayoutText($file)
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int, warnings: list<string>, items: list<array<string, mixed>>}
     */
    public function persistRows(Pricelist $pricelist, array $rows, string $defaultPricingMode = 'per_package'): array
    {
        $created = 0;
        $updated = 0;
        $warnings = [];
        $itemsOut = [];
        $defaultPricingMode = in_array($defaultPricingMode, ['per_package', 'per_test'], true)
            ? $defaultPricingMode
            : 'per_package';

        foreach ($rows as $index => $row) {
            $sampleTypeLabel = trim((string) ($row['sample_type'] ?? ''));
            $sampleType = $this->resolveSampleType($sampleTypeLabel);
            if ($sampleType === null) {
                $warnings[] = 'Row '.($index + 1).': unknown sample type «'.($sampleTypeLabel !== '' ? $sampleTypeLabel : '(blank)').'» — add it under Sample Types (or rename to match LIMS).';

                continue;
            }

            $paramTokens = $this->splitParameters((string) ($row['parameters'] ?? ''));
            $resolved = $this->resolveElements($paramTokens, $sampleType);
            $elements = $resolved['elements'];
            $unmatchedTokens = $resolved['unmatched'];

            if ($elements === []) {
                $paramHint = $paramTokens === []
                    ? 'no parameters were parsed from the file for this package'
                    : count($paramTokens).' parameter name(s) did not resolve to analysis elements under sample type «'.$sampleType->name.'»'
                        .(count($unmatchedTokens) > 0
                            ? ' (unmatched: '.implode(', ', array_slice($unmatchedTokens, 0, 5))
                                .(count($unmatchedTokens) > 5 ? ', …' : '').')'
                            : '')
                        .' — add those tests under this sample type in LIMS, or fix the import labels';
                $warnings[] = 'Row '.($index + 1).': no matching parameters for «'.$sampleTypeLabel.'»'
                    .(strcasecmp($sampleTypeLabel, (string) $sampleType->name) !== 0
                        ? ' (matched LIMS sample type «'.$sampleType->name.'»)'
                        : '')
                    .' — '.$paramHint.'.';

                continue;
            }

            if ($unmatchedTokens !== []) {
                $warnings[] = 'Row '.($index + 1).': «'.$sampleType->name.'» package imported with '
                    .count($elements).' parameter(s); skipped unmatched under this sample type: '
                    .implode(', ', array_slice($unmatchedTokens, 0, 8))
                    .(count($unmatchedTokens) > 8 ? ', …' : '').'.';
            }

            $mode = strtolower(trim((string) ($row['pricing_mode'] ?? $defaultPricingMode)));
            $isPackage = ($mode === 'per_package' || $mode === 'package' || $mode === '1' || $mode === 'yes')
                || ((bool) ($row['is_package'] ?? false));
            // Default tax true per Scope 3.
            $vat = array_key_exists('tax', $row)
                ? $this->truthy($row['tax'])
                : true;
            $sellingPrice = (float) ($row['selling_price'] ?? $row['unit_price'] ?? 0);
            $costPrice = array_key_exists('cost_price', $row) && $row['cost_price'] !== null && $row['cost_price'] !== ''
                ? $this->cleanNumber($row['cost_price'])
                : 0.0;

            if ($isPackage) {
                $analysisTypeId = (string) ($elements[0]->analysis_type_id ?? '');
                $item = PricelistItem::query()
                    ->where('pricelist_id', $pricelist->id)
                    ->where('sample_type_id', $sampleType->id)
                    ->where('is_package', true)
                    ->where('analysis_id', $analysisTypeId !== '' ? $analysisTypeId : null)
                    ->first();

                if ($item === null) {
                    $item = new PricelistItem();
                    $item->pricelist_id = $pricelist->id;
                    $item->sample_type_id = $sampleType->id;
                    $item->analysis_id = $analysisTypeId !== '' ? $analysisTypeId : null;
                    $item->is_package = true;
                    $item->active = true;
                    $item->internal_use = false;
                    $item->external_view = true;
                    $created++;
                } else {
                    $updated++;
                }

                $item->cost_price = $costPrice;
                $item->selling_price = $sellingPrice;
                $item->changed_price = $sellingPrice;
                $item->vat = $vat;
                $item->save();

                PricelistItemElement::query()->where('pricelist_item_id', $item->id)->delete();
                foreach ($elements as $element) {
                    PricelistItemElement::query()->create([
                        'pricelist_item_id' => $item->id,
                        'analysis_element_id' => $element->id,
                    ]);
                }

                $itemsOut[] = [
                    'id' => $item->id,
                    'is_package' => true,
                    'selling_price' => $sellingPrice,
                    'element_count' => count($elements),
                ];

                continue;
            }

            foreach ($elements as $element) {
                $item = PricelistItem::query()
                    ->where('pricelist_id', $pricelist->id)
                    ->where('sample_type_id', $sampleType->id)
                    ->where('analysis_element_id', $element->id)
                    ->where(function ($q) {
                        $q->where('is_package', false)->orWhereNull('is_package');
                    })
                    ->first();

                if ($item === null) {
                    $item = new PricelistItem();
                    $item->pricelist_id = $pricelist->id;
                    $item->sample_type_id = $sampleType->id;
                    $item->analysis_id = $element->analysis_type_id;
                    $item->analysis_element_id = $element->id;
                    $item->is_package = false;
                    $item->active = true;
                    $item->internal_use = false;
                    $item->external_view = true;
                    $created++;
                } else {
                    $updated++;
                }

                $item->cost_price = $costPrice;
                $item->selling_price = $sellingPrice;
                $item->changed_price = $sellingPrice;
                $item->vat = $vat;
                $item->save();

                $itemsOut[] = [
                    'id' => $item->id,
                    'is_package' => false,
                    'selling_price' => $sellingPrice,
                    'element_id' => $element->id,
                ];
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($warnings),
            'total_rows' => count($rows),
            'warnings' => $warnings,
            'items' => $itemsOut,
        ];
    }

    /**
     * Toast / banner title for an import result.
     *
     * @param  array{created?: int, updated?: int, skipped?: int, warnings?: list<string>}  $result
     */
    public function formatImportToastTitle(array $result): string
    {
        $created = (int) ($result['created'] ?? 0);
        $updated = (int) ($result['updated'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? count($result['warnings'] ?? []));
        $saved = $created + $updated;

        if ($saved === 0 && $skipped > 0) {
            return 'Pricelist import failed — nothing saved';
        }

        if ($skipped > 0) {
            return 'Pricelist import partial — '.$skipped.' package(s) skipped';
        }

        return 'Pricelist import complete';
    }

    /**
     * Human-readable import summary for toasts / flash / bell notifications.
     *
     * @param  array{created?: int, updated?: int, skipped?: int, total_rows?: int, warnings?: list<string>}  $result
     */
    public function formatImportSummary(array $result): string
    {
        $created = (int) ($result['created'] ?? 0);
        $updated = (int) ($result['updated'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? count($result['warnings'] ?? []));
        $totalRows = (int) ($result['total_rows'] ?? ($created + $updated + $skipped));
        $warnings = array_values(array_filter(array_map('strval', $result['warnings'] ?? [])));
        $saved = $created + $updated;

        $lines = [
            "Saved {$saved} package(s) ({$created} new, {$updated} updated) from {$totalRows} package row(s) in the file.",
        ];

        if ($skipped > 0) {
            $lines[] = '';
            $lines[] = "⚠ Skipped {$skipped} package(s) — fix LIMS catalogue, then re-import:";
            foreach (array_slice($warnings, 0, 8) as $warning) {
                $lines[] = '• '.$this->clarifyImportWarning($warning);
            }
            if (count($warnings) > 8) {
                $lines[] = '• (+'.(count($warnings) - 8).' more — see page banner)';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Shorten verbose persist warnings for toast / notification readability.
     */
    private function clarifyImportWarning(string $warning): string
    {
        if (preg_match('/unknown sample type «([^»]+)»/iu', $warning, $m)) {
            return 'Unknown sample type «'.$m[1].'» — add it under Sample Types (or rename the sheet), then re-import.';
        }

        if (preg_match('/no matching parameters for «([^»]+)»/iu', $warning, $m)) {
            $sample = $m[1];
            $unmatched = '';
            if (preg_match('/unmatched:\s*([^\)]+)\)/iu', $warning, $u)) {
                $unmatched = trim($u[1]);
            }

            if ($unmatched !== '') {
                return '«'.$sample.'» — no matching tests under this sample type ('
                    .$unmatched
                    .'). Add those analysis elements under «'.$sample.'», then re-import.';
            }

            return '«'.$sample.'» — no matching tests under this sample type. Add them in LIMS, then re-import.';
        }

        return $warning;
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::of($header)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();

        return match (true) {
            str_contains($key, 'sample') && str_contains($key, 'type') => 'sample_type',
            $key === 'sample' || $key === 'sample_description' => 'sample_type',
            str_contains($key, 'no_of_sample') || $key === 'qty' || $key === 'quantity' || $key === 'samples' => 'quantity',
            str_contains($key, 'parameter') || (str_contains($key, 'test') && ! str_contains($key, 'method')) => 'parameters',
            (str_contains($key, 'cost') && str_contains($key, 'price')) || $key === 'cost' => 'cost_price',
            (str_contains($key, 'selling') && str_contains($key, 'price')) || str_contains($key, 'unit_price') || $key === 'price' => 'selling_price',
            $key === 'tax' || str_contains($key, 'vat') => 'tax',
            str_contains($key, 'pricing_mode') || str_contains($key, 'package') => 'pricing_mode',
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
            // First non-empty wins for duplicate keys (avoid later columns overwriting Sample).
            if (array_key_exists($key, $mapped) && trim((string) ($mapped[$key] ?? '')) !== '') {
                continue;
            }
            $mapped[$key] = $line[$col] ?? null;
        }

        $sample = trim((string) ($mapped['sample_type'] ?? ''));
        $params = trim((string) ($mapped['parameters'] ?? ''));
        if ($sample === '' && $params === '') {
            return null;
        }

        $mapped['selling_price'] = $this->cleanNumber($mapped['selling_price'] ?? $mapped['unit_price'] ?? 0);
        if (array_key_exists('cost_price', $mapped) && $mapped['cost_price'] !== null && $mapped['cost_price'] !== '') {
            $mapped['cost_price'] = $this->cleanNumber($mapped['cost_price']);
        }

        return $mapped;
    }

    /**
     * Amspec Excel package layout: Sample / Unit Price sit on the first row of a merged
     * block; follow-on rows only have Test (and Method). Carry sample forward and merge
     * tests into one per-package commercial row.
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
            $sellingPrice = (float) ($row['selling_price'] ?? $row['unit_price'] ?? 0);
            $costPrice = array_key_exists('cost_price', $row) && $row['cost_price'] !== null && $row['cost_price'] !== ''
                ? $this->cleanNumber($row['cost_price'])
                : 0.0;

            if ($this->isExcelFooterLabel($sample) || $this->isExcelFooterLabel($params)) {
                if ($open !== null) {
                    $packages[] = $this->finalizeExcelPackage($open);
                    $open = null;
                }

                continue;
            }

            if ($sample !== '') {
                if ($open !== null) {
                    $packages[] = $this->finalizeExcelPackage($open);
                }

                $open = $row;
                $open['sample_type'] = $sample;
                $open['_tests'] = $params !== '' ? [$params] : [];
                $open['selling_price'] = $sellingPrice;
                $open['cost_price'] = $costPrice;

                continue;
            }

            if ($params === '' || $open === null) {
                continue;
            }

            $open['_tests'][] = $params;
            if ($sellingPrice > 0 && (float) ($open['selling_price'] ?? 0) <= 0) {
                $open['selling_price'] = $sellingPrice;
            }
            if ($costPrice > 0 && (float) ($open['cost_price'] ?? 0) <= 0) {
                $open['cost_price'] = $costPrice;
            }
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
        /** @var list<string> $tests */
        $tests = array_values(array_filter(
            array_map('trim', is_array($open['_tests'] ?? null) ? $open['_tests'] : []),
            fn (string $t): bool => $t !== '' && ! is_numeric($t)
        ));
        unset($open['_tests']);

        $open['parameters'] = implode('; ', array_values(array_unique($tests)));
        $open['pricing_mode'] = $open['pricing_mode'] ?? 'per_package';
        $open['is_package'] = true;

        return $open;
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
     * Resolve parameter tokens to analysis elements that belong to the given sample type.
     * Does not fall back to elements under other sample types (avoids Hand Swab packages
     * picking up E. coli / Enterobacteriaceae from unrelated catalogs).
     *
     * @param  list<string>  $tokens
     * @return array{elements: list<AnalysisElements>, unmatched: list<string>}
     */
    private function resolveElements(array $tokens, SampleType $sampleType): array
    {
        $elements = [];
        $unmatched = [];
        $seenElementIds = [];

        foreach ($tokens as $token) {
            $analyte = $this->resolveAnalyte($token);
            if ($analyte === null) {
                $unmatched[] = $token;
                continue;
            }

            $element = AnalysisElements::query()
                ->where('analyte_id', $analyte->id)
                ->whereHas('analysis_type', function ($query) use ($sampleType) {
                    $query->where('sample_type_id', $sampleType->id);
                })
                ->orderBy('id')
                ->first();

            if ($element === null) {
                $unmatched[] = $token;
                continue;
            }

            $elementId = (string) $element->id;
            if (isset($seenElementIds[$elementId])) {
                continue;
            }
            $seenElementIds[$elementId] = true;
            $elements[] = $element;
        }

        return [
            'elements' => $elements,
            'unmatched' => $unmatched,
        ];
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

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $raw = strtolower(trim((string) $value));

        return in_array($raw, ['1', 'true', 'yes', 'y', 'vat', 'on'], true);
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
