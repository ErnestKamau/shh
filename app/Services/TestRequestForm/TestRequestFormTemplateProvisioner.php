<?php

namespace App\Services\TestRequestForm;

use App\Models\SubmissionForm;
use App\Models\TestRequestForm;
use App\SampleType;
use Database\Seeders\TestRequestFormFoodSeeder;
use Database\Seeders\TestRequestFormWasteWaterSeeder;
use Database\Seeders\TestRequestFormWaterSeeder;
use Illuminate\Database\Seeder;

final class TestRequestFormTemplateProvisioner
{
    private const DOCUMENT_CODE_FOOD = 'TRF-FOOD-019';

    private const DOCUMENT_CODE_WATER = 'TRF-WATER-020';

    private const DOCUMENT_CODE_WASTE_WATER = 'TRF-WASTE-036';

    public function ensureSubmissionFormForSampleType(string $sampleTypeId): ?SubmissionForm
    {
        $sampleTypeId = trim($sampleTypeId);
        if ($sampleTypeId === '') {
            return null;
        }

        $existing = $this->findLinkedSubmissionForm($sampleTypeId);
        if ($existing !== null) {
            return $existing;
        }

        $sampleType = SampleType::query()->find($sampleTypeId);
        if ($sampleType === null) {
            return null;
        }

        TestRequestForm::seedDefaults();

        $this->runSeederForSampleType($sampleType);
        $this->linkSampleTypeToCanonicalForm($sampleType);

        return $this->findLinkedSubmissionForm($sampleTypeId);
    }

    private function findLinkedSubmissionForm(string $sampleTypeId): ?SubmissionForm
    {
        return SubmissionForm::query()
            ->where('is_active', true)
            ->where('is_published', true)
            ->where(function ($builder): void {
                $builder->where('document_code', 'like', 'TRF-%')
                    ->orWhere('document_code', 'LSR-001')
                    ->orWhereRaw('lower(name) like ?', ['%test request form%']);
            })
            ->whereHas(
                'sampleTypes',
                fn ($query) => $query->where('sample_types.id', $sampleTypeId)
            )
            ->orderByRaw("case when document_code like 'TRF-%' then 0 when document_code = 'LSR-001' then 1 else 2 end")
            ->first();
    }

    private function runSeederForSampleType(SampleType $sampleType): void
    {
        $classification = TestRequestForm::classifySampleType($sampleType);

        $seeder = match (true) {
            $classification['is_food'] => new TestRequestFormFoodSeeder,
            $classification['is_waste_water'] => new TestRequestFormWasteWaterSeeder,
            $classification['is_water'] => new TestRequestFormWaterSeeder,
            default => new TestRequestFormWaterSeeder,
        };

        if ($seeder instanceof Seeder) {
            $seeder->setContainer(app());
            $seeder->run();
        }
    }

    private function linkSampleTypeToCanonicalForm(SampleType $sampleType): void
    {
        $documentCode = $this->resolveDocumentCode($sampleType);
        $form = SubmissionForm::query()
            ->where('document_code', $documentCode)
            ->first();

        if ($form === null) {
            return;
        }

        if (! $form->sampleTypes()->where('sample_types.id', $sampleType->id)->exists()) {
            $form->sampleTypes()->attach($sampleType->id);
        }
    }

    private function resolveDocumentCode(SampleType $sampleType): string
    {
        $classification = TestRequestForm::classifySampleType($sampleType);

        if ($classification['is_food']) {
            return self::DOCUMENT_CODE_FOOD;
        }

        if ($classification['is_waste_water']) {
            return self::DOCUMENT_CODE_WASTE_WATER;
        }

        return self::DOCUMENT_CODE_WATER;
    }
}
