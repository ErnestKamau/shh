<?php

namespace App\Services\Lab;

use App\AnalysisType;
use App\SampleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CopyAnalysisTypeToSampleTypesService
{
    /**
     * Copy one analysis type (and its parameters) onto other sample types.
     *
     * @param  list<string>  $targetSampleTypeIds
     * @return array{
     *     copied: list<array{sample_type_id: string, sample_type_name: string, analysis_type_id: string}>,
     *     skipped: list<array{sample_type_id: string, sample_type_name: string, reason: string}>
     * }
     */
    public function copy(string $sourceAnalysisTypeId, array $targetSampleTypeIds): array
    {
        $targetIds = array_values(array_unique(array_filter(
            array_map(static fn ($id): string => (string) $id, $targetSampleTypeIds),
            static fn (string $id): bool => $id !== '' && Str::isUuid($id)
        )));

        if ($targetIds === []) {
            throw new InvalidArgumentException('Select at least one target sample type.');
        }

        $source = AnalysisType::query()
            ->with(['analysis_elements', 'labs', 'invoicableItems'])
            ->findOrFail($sourceAnalysisTypeId);

        $targets = SampleType::query()
            ->whereIn('id', $targetIds)
            ->get()
            ->keyBy(fn (SampleType $sampleType): string => (string) $sampleType->id);

        $copied = [];
        $skipped = [];

        DB::transaction(function () use ($source, $targetIds, $targets, &$copied, &$skipped): void {
            foreach ($targetIds as $targetId) {
                $target = $targets->get($targetId);
                if (! $target) {
                    $skipped[] = [
                        'sample_type_id' => $targetId,
                        'sample_type_name' => 'Unknown',
                        'reason' => 'Sample type not found.',
                    ];

                    continue;
                }

                if ((string) $source->sample_type_id === $targetId) {
                    $skipped[] = [
                        'sample_type_id' => $targetId,
                        'sample_type_name' => (string) $target->name,
                        'reason' => 'Already belongs to this sample type.',
                    ];

                    continue;
                }

                $existing = AnalysisType::query()
                    ->where('sample_type_id', $targetId)
                    ->whereRaw('LOWER(TRIM(code)) = ?', [strtolower(trim((string) $source->code))])
                    ->first();

                if ($existing) {
                    $skipped[] = [
                        'sample_type_id' => $targetId,
                        'sample_type_name' => (string) $target->name,
                        'reason' => 'An analysis type with the same code already exists.',
                    ];

                    continue;
                }

                $newAnalysisType = $this->copyToSampleType($source, $target);

                $copied[] = [
                    'sample_type_id' => $targetId,
                    'sample_type_name' => (string) $target->name,
                    'analysis_type_id' => (string) $newAnalysisType->id,
                ];
            }
        });

        return [
            'copied' => $copied,
            'skipped' => $skipped,
        ];
    }

    private function copyToSampleType(AnalysisType $source, SampleType $target): AnalysisType
    {
        $nextLevel = (int) (AnalysisType::query()
            ->where('sample_type_id', $target->id)
            ->max('level') ?? 0) + 1;

        $copy = $source->replicate([
            'zoho_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
        $copy->sample_type_id = $target->id;
        $copy->level = $nextLevel;
        $copy->zoho_id = null;
        $copy->save();

        foreach ($source->analysis_elements as $element) {
            $newElement = $element->replicate([
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
            $newElement->analysis_type_id = $copy->id;
            if (empty($newElement->lab_section_id) && ! empty($copy->lab_section_id)) {
                $newElement->lab_section_id = $copy->lab_section_id;
            }
            $newElement->save();
        }

        if (Schema::hasTable('analysis_type_lab_relation')) {
            $labIds = $source->labs->pluck('id')->map(fn ($id): string => (string) $id)->all();
            if ($labIds === [] && $source->lab_id) {
                $labIds = [(string) $source->lab_id];
            }
            $copy->labs()->sync($labIds);
        }

        if (Schema::hasTable('analysis_type_invoicable_item')) {
            $invoicableIds = $source->invoicableItems->pluck('id')->map(fn ($id): string => (string) $id)->all();
            $copy->invoicableItems()->sync($invoicableIds);
        }

        return $copy;
    }
}
