<?php

namespace App\Imports\Lab;

use App\AnalysisType;
use App\Imports\BaseImporter;
use App\Lab;
use App\SampleType;

class AnalysisTypeImporter extends BaseImporter
{
    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['code'] ?? null)) {
            $errors[] = 'Code is required';
        }

        if (empty($row['name'] ?? null)) {
            $errors[] = 'Name is required';
        }

        if (empty($row['sample_type_code'] ?? null)) {
            $errors[] = 'Sample type code is required';
        } else {
            $stCode = trim($row['sample_type_code']);
            $stExists = SampleType::where('company_id', $this->batch->company_id)
                ->where(function ($q) use ($stCode) {
                    $q->where('code', $stCode)
                        ->orWhere('name', $stCode)
                        ->orWhere('code', 'like', "%{$stCode}%")
                        ->orWhere('name', 'like', "%{$stCode}%");
                })
                ->exists();

            if (! $stExists) {
                $errors[] = "Sample type '{$row['sample_type_code']}' does not exist — import Sample Types first";
            }
        }

        if (empty($row['lab_code'] ?? null)) {
            $errors[] = 'Lab code is required';
        } else {
            $lCode = trim($row['lab_code']);
            $lExists = Lab::where('company_id', $this->batch->company_id)
                ->where(function ($q) use ($lCode) {
                    $q->where('code', $lCode)
                        ->orWhere('name', $lCode)
                        ->orWhere('code', 'like', "%{$lCode}%")
                        ->orWhere('name', 'like', "%{$lCode}%");
                })
                ->exists();

            if (! $lExists) {
                $errors[] = "Lab '{$row['lab_code']}' does not exist — import Labs first";
            }
        }

        return $errors;
    }

    protected function transformRow(array $row): mixed
    {
        $stCode = trim($row['sample_type_code'] ?? '');
        $sampleType = SampleType::where('company_id', $this->batch->company_id)
            ->where(function ($q) use ($stCode) {
                $q->where('code', $stCode)
                    ->orWhere('name', $stCode)
                    ->orWhere('code', 'like', "%{$stCode}%")
                    ->orWhere('name', 'like', "%{$stCode}%");
            })
            ->first();

        $lCode = trim($row['lab_code'] ?? '');
        $lab = Lab::where('company_id', $this->batch->company_id)
            ->where(function ($q) use ($lCode) {
                $q->where('code', $lCode)
                    ->orWhere('name', $lCode)
                    ->orWhere('code', 'like', "%{$lCode}%")
                    ->orWhere('name', 'like', "%{$lCode}%");
            })
            ->first();

        return [
            'code' => $row['code'],
            'name' => $row['name'] ?? ('Analysis Type '.$row['code']),
            'sample_type_id' => $sampleType?->id,
            'lab_id' => $lab?->id,
            'has_no_result' => $row['has_no_result'] ?? 0,
            'reporting_time' => $row['reporting_time'] ?? null,
            'company_id' => $this->batch->company_id,
            'active' => 1,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            if (empty($transformedData['sample_type_id'])) {
                throw new \Exception('Sample type could not be resolved');
            }

            // Scope by sample type so the same analysis-type code can exist under different matrices.
            $analysisType = AnalysisType::updateOrCreate(
                [
                    'code' => $transformedData['code'],
                    'sample_type_id' => $transformedData['sample_type_id'],
                    'company_id' => $this->batch->company_id,
                ],
                $transformedData
            );

            if (! empty($transformedData['lab_id']) && method_exists($analysisType, 'labs')) {
                try {
                    $analysisType->labs()->sync([$transformedData['lab_id']]);
                } catch (\Throwable $t) {
                    \Log::warning('Could not sync labs for AnalysisType import: '.$t->getMessage());
                }
            }

            $this->recordUpsert($transformedData['code'], 'upserted');

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analysis type: {$e->getMessage()}");
        }
    }
}
