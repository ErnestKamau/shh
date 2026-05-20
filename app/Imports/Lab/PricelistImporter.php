<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use Illuminate\Support\Str;

class PricelistImporter extends BaseImporter
{
    private function cleanPrice($price): string
    {
        if (is_null($price) || trim((string)$price) === '') {
            return '0.0';
        }
        
        $priceStr = trim((string)$price);
        
        // Remove commas (thousands-separators)
        $priceStr = str_replace(',', '', $priceStr);
        
        // Match first occurring numeric/decimal value (with optional negative sign)
        if (preg_match('/-?\d+(?:\.\d+)?/', $priceStr, $matches)) {
            return $matches[0];
        }
        
        return '0.0';
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['pricelist_code'] ?? null)) {
            $errors[] = 'Pricelist code is required';
        }

        if (empty($row['pricelist_name'] ?? null)) {
            $errors[] = 'Pricelist name is required';
        }

        if (empty($row['currency_code'] ?? null)) {
            $errors[] = 'Currency code is required';
        }

        if (empty($row['sample_type'] ?? null)) {
            $errors[] = 'Sample type is required';
        }

        if (empty($row['parameter'] ?? null)) {
            $errors[] = 'Parameter is required';
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        // Normalize is_master from "Yes", "No", 1, 0, etc.
        $isMasterRaw = strtolower(trim((string)($row['is_master'] ?? '')));
        $isMaster = ($isMasterRaw === 'yes' || $isMasterRaw === '1' || $isMasterRaw === 'true') ? 1 : 0;

        $price = $this->cleanPrice($row['price'] ?? '0.0');

        return [
            'pricelist_code' => $row['pricelist_code'],
            'pricelist_name' => $row['pricelist_name'] ?? ('Pricelist ' . $row['pricelist_code']),
            'is_master' => $isMaster,
            'currency_code' => $row['currency_code'] ?? 'TZS',
            'valid_till' => $row['valid_till'] ?? null,
            'sample_type' => $row['sample_type'],
            'parameter' => $row['parameter'],
            'price' => !empty($price) ? (float) $price : 0.0,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            // Find or create pricelist
            $pricelist = Pricelist::where('code', $transformedData['pricelist_code'])->first();
            if (!$pricelist) {
                $pricelist = Pricelist::create([
                    'code' => $transformedData['pricelist_code'],
                    'description' => $transformedData['pricelist_name'],
                    'is_master' => (bool)$transformedData['is_master'],
                    'currency_id' => $this->getCurrencyId($transformedData['currency_code']),
                    'valid_till' => $transformedData['valid_till'] ? date('Y-m-d', strtotime($transformedData['valid_till'])) : null,
                    'document_no' => $transformedData['pricelist_code'],
                    'revision_number' => '1.0',
                    'status' => 'no-changes',
                    'active' => true,
                ]);
            } else {
                $pricelist->update([
                    'description' => $transformedData['pricelist_name'],
                    'is_master' => (bool)$transformedData['is_master'],
                    'currency_id' => $this->getCurrencyId($transformedData['currency_code']),
                    'valid_till' => $transformedData['valid_till'] ? date('Y-m-d', strtotime($transformedData['valid_till'])) : null,
                ]);
            }

            // Find the SampleType (aligned with system database scoped/unscoped, case, spaces)
            $sampleTypeStr = trim($transformedData['sample_type']);
            $sampleType = \App\SampleType::where('company_id', $this->batch->company_id)
                ->where(function($q) use ($sampleTypeStr) {
                    $q->where('name', $sampleTypeStr)
                      ->orWhere('code', $sampleTypeStr);
                })->first();

            if (!$sampleType) {
                $sampleType = \App\SampleType::where(function($q) use ($sampleTypeStr) {
                    $q->where('name', $sampleTypeStr)
                      ->orWhere('code', $sampleTypeStr);
                })->first();
            }

            if (!$sampleType) {
                $sampleType = \App\SampleType::where('company_id', $this->batch->company_id)
                    ->where(function($q) use ($sampleTypeStr) {
                        $q->where(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(name))'), strtolower(trim($sampleTypeStr)))
                          ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(code))'), strtolower(trim($sampleTypeStr)));
                    })->first();
            }

            if (!$sampleType) {
                $sampleType = \App\SampleType::where(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(name))'), strtolower(trim($sampleTypeStr)))
                    ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(code))'), strtolower(trim($sampleTypeStr)))
                    ->first();
            }

            if (!$sampleType) {
                // Auto-create SampleType if not found
                $sampleTypeCode = $this->resolveCodeFromName($sampleTypeStr);
                $sampleType = \App\SampleType::where('code', $sampleTypeCode)->first();
                if (!$sampleType) {
                    $sampleType = \App\SampleType::create([
                        'code' => $sampleTypeCode,
                        'company_id' => $this->batch->company_id,
                        'name' => $sampleTypeStr,
                        'is_results_attachable' => 1,
                        'disposal_count' => 30,
                        'active' => 1
                    ]);
                    $this->recordUpsert($sampleTypeCode, 'created');
                }
            }

            // Find the Analyte (parameter) aligned with system database scoped/unscoped, case, spaces
            $parameterStr = trim($transformedData['parameter']);
            $analyte = \App\Analyte::where('company_id', $this->batch->company_id)
                ->where(function($q) use ($parameterStr) {
                    $q->where('name', $parameterStr)
                      ->orWhere('code', $parameterStr);
                })->first();

            if (!$analyte) {
                $analyte = \App\Analyte::where(function($q) use ($parameterStr) {
                    $q->where('name', $parameterStr)
                      ->orWhere('code', $parameterStr);
                })->first();
            }

            if (!$analyte) {
                $analyte = \App\Analyte::where('company_id', $this->batch->company_id)
                    ->where(function($q) use ($parameterStr) {
                        $q->where(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(name))'), strtolower(trim($parameterStr)))
                          ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(code))'), strtolower(trim($parameterStr)));
                    })->first();
            }

            if (!$analyte) {
                $analyte = \App\Analyte::where(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(name))'), strtolower(trim($parameterStr)))
                    ->orWhere(\Illuminate\Support\Facades\DB::raw('LOWER(TRIM(code))'), strtolower(trim($parameterStr)))
                    ->first();
            }

            if (!$analyte) {
                // Auto-create Analyte if not found
                $analyteCode = $this->resolveCodeFromName($parameterStr);
                $analyte = \App\Analyte::where('code', $analyteCode)->first();
                if (!$analyte) {
                    $analyte = \App\Analyte::create([
                        'code' => $analyteCode,
                        'company_id' => $this->batch->company_id,
                        'name' => $parameterStr,
                        'decimal_places' => 2,
                        'reporting_symbol' => null,
                        'reporting_unit' => null,
                        'non_detectable' => 0,
                        'non_accredited' => 0,
                        'show_on_report' => 1,
                        'active' => 1
                    ]);
                    $this->recordUpsert($analyteCode, 'created');
                }
            }

            // Find matching AnalysisElements under this SampleType
            $analysisElements = \App\AnalysisElements::where('analyte_id', $analyte->id)
                ->whereHas('analysis_type', function ($query) use ($sampleType) {
                    $query->where('sample_type_id', $sampleType->id);
                })->get();

            // Fallback: If no AnalysisElements exists yet, establish it automatically
            if ($analysisElements->isEmpty()) {
                $analysisType = \App\AnalysisType::where('sample_type_id', $sampleType->id)
                    ->where('company_id', $this->batch->company_id)
                    ->first();
                if (!$analysisType) {
                    $firstLab = \App\Lab::where('company_id', $this->batch->company_id)->first() ?? \App\Lab::first();
                    $labId = $firstLab ? $firstLab->id : null;
                    if (!$labId) {
                        $defaultLab = \App\Lab::create([
                            'code' => 'LAB-DEFAULT',
                            'name' => 'Default Lab',
                            'active' => 1,
                            'company_id' => $this->batch->company_id
                        ]);
                        $labId = $defaultLab->id;
                    }

                    $analysisType = \App\AnalysisType::create([
                        'sample_type_id' => $sampleType->id,
                        'company_id' => $this->batch->company_id,
                        'lab_id' => $labId,
                        'name' => $sampleType->name . ' Analysis',
                        'code' => 'AT-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $sampleType->code), 0, 10)) . '-GEN',
                        'active' => true,
                    ]);

                    // Sync lab relation if the relation table exists
                    try {
                        if ($labId && method_exists($analysisType, 'labs')) {
                            $analysisType->labs()->sync([$labId]);
                        }
                    } catch (\Throwable $t) {
                        \Log::warning("Could not sync labs for AnalysisType in PricelistImporter: " . $t->getMessage());
                    }
                }

                $newElement = \App\AnalysisElements::create([
                    'analysis_type_id' => $analysisType->id,
                    'analyte_id' => $analyte->id,
                    'active' => true,
                    'show_on_report' => true,
                    'decimal_places' => $analyte->decimal_places ?? 2,
                    'reporting_unit' => $analyte->reporting_unit,
                ]);

                $analysisElements = collect([$newElement]);
            }

            // Insert or update PricelistItem for each matching AnalysisElements record
            $price = $transformedData['price'];
            $importedCount = 0;

            foreach ($analysisElements as $element) {
                $item = PricelistItem::where('pricelist_id', $pricelist->id)
                    ->where('sample_type_id', $sampleType->id)
                    ->where('analysis_id', $element->analysis_type_id)
                    ->where('analysis_element_id', $element->id)
                    ->first();

                if ($item) {
                    $item->update([
                        'selling_price' => $price,
                        'changed_price' => $price,
                    ]);
                } else {
                    PricelistItem::create([
                        'id' => (string) Str::uuid(),
                        'pricelist_id' => $pricelist->id,
                        'sample_type_id' => $sampleType->id,
                        'analysis_id' => $element->analysis_type_id,
                        'analysis_element_id' => $element->id,
                        'cost_price' => 0.0,
                        'selling_price' => $price,
                        'changed_price' => $price,
                        'vat' => false,
                        'internal_use' => false,
                        'external_view' => true,
                        'active' => true,
                    ]);
                }
                $importedCount++;
            }

            if ($importedCount > 0) {
                $this->recordUpsert($transformedData['pricelist_code'] . '-' . $transformedData['parameter'], 'updated');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import pricelist row: {$e->getMessage()}");
        }
    }

    protected function getCurrencyId(string $code): ?string
    {
        $code = trim(strtoupper($code));
        if ($code === '') {
            return null;
        }

        $currency = \App\Models\Currency::where('code', $code)
            ->first();

        if (!$currency) {
            $currency = \App\Models\Currency::where('active', true)->first()
                ?? \App\Models\Currency::first();
        }

        return $currency?->id;
    }

    protected function resolveCodeFromName(string $name): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        return strtoupper(substr($slug, 0, 100));
    }
}
