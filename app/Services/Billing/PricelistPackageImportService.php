<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\SampleType;
use Illuminate\Http\UploadedFile;
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
 * Tax defaults to true (VAT on) unless the sheet says otherwise.
 * Quantity is NOT stored on pricelist items — it is entered on the quotation line.
 *
 * PDF uses pdftotext (poppler-utils); when missing, use Excel.
 */
class PricelistPackageImportService
{
    public function __construct(
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

        return $rows;
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
            $sampleType = $this->resolveSampleType((string) ($row['sample_type'] ?? ''));
            if ($sampleType === null) {
                $warnings[] = 'Row '.($index + 1).': unknown sample type';

                continue;
            }

            $paramTokens = $this->splitParameters((string) ($row['parameters'] ?? ''));
            $elements = $this->resolveElements($paramTokens);
            if ($elements === []) {
                $warnings[] = 'Row '.($index + 1).': no matching parameters';

                continue;
            }

            $mode = strtolower(trim((string) ($row['pricing_mode'] ?? $defaultPricingMode)));
            $isPackage = ($mode === 'per_package' || $mode === 'package' || $mode === '1' || $mode === 'yes')
                || ((bool) ($row['is_package'] ?? false));
            // Default tax true per Scope 3.
            $vat = array_key_exists('tax', $row)
                ? $this->truthy($row['tax'])
                : true;
            $unitPrice = (float) ($row['unit_price'] ?? 0);

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
                    $created++;
                } else {
                    $updated++;
                }

                $item->selling_price = $unitPrice;
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
                    'selling_price' => $unitPrice,
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
                    $created++;
                } else {
                    $updated++;
                }

                $item->selling_price = $unitPrice;
                $item->vat = $vat;
                $item->save();

                $itemsOut[] = [
                    'id' => $item->id,
                    'is_package' => false,
                    'selling_price' => $unitPrice,
                    'element_id' => $element->id,
                ];
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'warnings' => $warnings,
            'items' => $itemsOut,
        ];
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::of($header)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();

        return match (true) {
            str_contains($key, 'sample') => 'sample_type',
            str_contains($key, 'parameter') || str_contains($key, 'test') => 'parameters',
            str_contains($key, 'unit_price') || $key === 'price' || str_contains($key, 'selling') => 'unit_price',
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
            $mapped[$key] = $line[$col] ?? null;
        }

        if (trim((string) ($mapped['sample_type'] ?? '')) === '') {
            return null;
        }

        $mapped['unit_price'] = $this->cleanNumber($mapped['unit_price'] ?? 0);

        return $mapped;
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
     * @return list<AnalysisElements>
     */
    private function resolveElements(array $tokens): array
    {
        $elements = [];
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
                $elements[] = $element;
            }
        }

        return $elements;
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
