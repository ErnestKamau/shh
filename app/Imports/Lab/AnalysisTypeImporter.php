<?php

namespace App\Imports\Lab;

use App\Imports\BaseImporter;
use App\AnalysisType;
use App\SampleType;
use App\Lab;

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

            if (!$stExists) {
                $errors[] = "Sample type '{$row['sample_type_code']}' does not exist";
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

            if (!$lExists) {
                $errors[] = "Lab '{$row['lab_code']}' does not exist";
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

        if (!$sampleType) {
            $sampleType = SampleType::where('company_id', $this->batch->company_id)->first();
        }
        if (!$lab) {
            $lab = Lab::where('company_id', $this->batch->company_id)->first();
        }

        return [
            'code' => $row['code'],
            'name' => $row['name'] ?? ('Analysis Type ' . $row['code']),
            'sample_type_id' => $sampleType?->id,
            'lab_id' => $lab?->id,
            'has_no_result' => $row['has_no_result'] ?? 0,
            'reporting_time' => $row['reporting_time'] ?? null,
            'company_id' => $this->batch->company_id,
        ];
    }

    protected function importRow(array $transformedData, array $originalRow): bool
    {
        try {
            AnalysisType::updateOrCreate(
                ['code' => $transformedData['code'], 'company_id' => $this->batch->company_id],
                $transformedData
            );

            $this->recordUpsert($transformedData['code'], 'inserted');
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to import analysis type: {$e->getMessage()}");
        }
    }
}
