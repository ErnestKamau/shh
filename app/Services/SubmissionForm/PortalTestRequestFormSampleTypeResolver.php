<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\SampleType;
use Illuminate\Support\Collection;

class PortalTestRequestFormSampleTypeResolver
{
    /**
     * @return Collection<int, SampleType>
     */
    public function resolveForForm(SubmissionForm $form): Collection
    {
        $linked = $form->relationLoaded('sampleTypes')
            ? $form->sampleTypes
            : $form->sampleTypes()->get();

        if ($linked->isNotEmpty()) {
            return $linked;
        }

        $documentCode = strtoupper((string) $form->document_code);
        $formName = strtoupper((string) $form->name);

        $query = SampleType::query()->where('active', true);

        if (str_contains($documentCode, 'FOOD') || str_contains($formName, 'FOOD')) {
            $query->where(function ($builder): void {
                $builder->where('name', 'ilike', '%food%')
                    ->orWhere('code', 'ilike', '%food%');
            });
        } elseif (
            (str_contains($documentCode, 'WATER') || str_contains($formName, 'WATER'))
            && ! str_contains($documentCode, 'WASTE')
            && ! str_contains($formName, 'WASTE')
        ) {
            $query->where(function ($builder): void {
                $builder->where(function ($water): void {
                    $water->where('name', 'ilike', '%water%')
                        ->orWhere('code', 'ilike', '%water%')
                        ->orWhere('code', 'ilike', '%wtr%');
                })->where('name', 'not ilike', '%waste%')
                    ->where('code', 'not ilike', '%waste%');
            });
        } else {
            return collect();
        }

        return $query->orderBy('name')->get();
    }

    /**
     * @return list<array{id: string, name: string, code: string|null}>
     */
    public function mapForApi(SubmissionForm $form): array
    {
        return $this->resolveForForm($form)
            ->map(fn (SampleType $type): array => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
                'code' => $type->code,
            ])
            ->values()
            ->all();
    }
}
