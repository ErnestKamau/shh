<?php

namespace App\Services\GroupedWorksheets;

use App\AnalysisType;
use App\Analyte;
use App\CapturedResult;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\SampleHeader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupedResultsCaptureService
{
    /**
     * @return Collection<int, CapturedResult>
     */
    public function loadCapturedResults(SampleHeader $batch, GroupedWorksheetHolder $holder): Collection
    {
        return CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->where('has_grouped_worksheet', true)
            ->where('has_no_result_capture', false)
            ->orderBy('analysis_type_order')
            ->orderBy('parameters_order')
            ->orderBy('sample_detail_code')
            ->get();
    }

    /**
     * @param  Collection<int, CapturedResult>  $capturedResults
     * @return array{
     *     parameters: array<int, array{row_key: string, label: string, analysis_type_name: string|null}>,
     *     samples: array<int, array{sample_detail_code: string, sample_detail_id: string|null}>,
     *     cells: array<string, array<string, array{captured_result_id: string, result: string|null, reporting_symbol: string|null}>>
     * }
     */
    public function buildMatrix(Collection $capturedResults): array
    {
        if ($capturedResults->isEmpty()) {
            return [
                'parameters' => [],
                'samples' => [],
                'cells' => [],
            ];
        }

        $analysisTypeIds = $capturedResults->pluck('analysis_type_id')->unique()->filter()->values();
        $analysisTypes = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->get()
            ->keyBy('id');

        $analyteIds = $capturedResults->pluck('analyte_id')->unique()->filter()->values();
        $analytes = Analyte::query()
            ->whereIn('id', $analyteIds)
            ->get()
            ->keyBy('id');

        $rowMeta = [];
        $samples = [];
        $cells = [];

        foreach ($capturedResults as $result) {
            $sampleCode = (string) ($result->sample_detail_code ?? '');
            if ($sampleCode === '') {
                continue;
            }

            $analysisType = $analysisTypes->get((string) $result->analysis_type_id);
            $analysisTypeName = $analysisType?->name ?? $analysisType?->code ?? null;
            $analyte = $analytes->get((string) $result->analyte_id);
            $analyteName = $this->resolveAnalyteName($result, $analyte);
            $rowKey = $this->rowKey((string) $result->analysis_type_id, (string) $result->analyte_id);

            if (! isset($rowMeta[$rowKey])) {
                $label = $analyteName;
                if ($analysisTypeName && $this->analyteAppearsUnderMultipleTypes($capturedResults, (string) $result->analyte_id)) {
                    $label = $analyteName.' ('.$analysisTypeName.')';
                }

                $rowMeta[$rowKey] = [
                    'row_key' => $rowKey,
                    'label' => $label,
                    'analysis_type_name' => $analysisTypeName,
                    'sort_order' => ($result->analysis_type_order ?? 0) * 10000 + ($result->parameters_order ?? 0),
                ];
            }

            if (! isset($samples[$sampleCode])) {
                $samples[$sampleCode] = [
                    'sample_detail_code' => $sampleCode,
                    'sample_detail_id' => $result->sample_detail_id ? (string) $result->sample_detail_id : null,
                ];
            }

            $cells[$rowKey][$sampleCode] = [
                'captured_result_id' => (string) $result->id,
                'result' => $result->result,
                'reporting_symbol' => $result->result_reporting_symbol,
            ];
        }

        uasort($rowMeta, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);
        ksort($samples, SORT_NATURAL);

        $parameters = array_values(array_map(
            fn (array $row) => [
                'row_key' => $row['row_key'],
                'label' => $row['label'],
                'analysis_type_name' => $row['analysis_type_name'],
            ],
            $rowMeta
        ));

        return [
            'parameters' => $parameters,
            'samples' => array_values($samples),
            'cells' => $cells,
        ];
    }

    /**
     * @param  array<string, array{captured_result_id: string, result?: string|null, reporting_symbol?: string|null}>  $payload  keyed by captured_result_id
     */
    public function saveCellResults(array $payload): void
    {
        if ($payload === []) {
            return;
        }

        $userId = Auth::id();

        DB::transaction(function () use ($payload, $userId): void {
            foreach ($payload as $capturedResultId => $data) {
                $captured = CapturedResult::query()->find($capturedResultId);
                if (! $captured) {
                    continue;
                }

                $captured->result = $data['result'] ?? null;
                $captured->result_reporting_symbol = $data['reporting_symbol'] ?? null;
                $captured->assignAnalyst($userId ? (string) $userId : null);
                $captured->save();
            }
        });
    }

    protected function rowKey(string $analysisTypeId, string $analyteId): string
    {
        return $analysisTypeId.'|'.$analyteId;
    }

    protected function analyteAppearsUnderMultipleTypes(Collection $capturedResults, string $analyteId): bool
    {
        return $capturedResults
            ->where('analyte_id', $analyteId)
            ->pluck('analysis_type_id')
            ->unique()
            ->count() > 1;
    }

    protected function resolveAnalyteName(CapturedResult $result, ?Analyte $analyte): string
    {
        $analyteName = $analyte?->name;
        $analyteCode = $result->analyte_code;

        if (! $analyteName && $analyte) {
            $analyteName = $analyte->code;
        }

        if (! $analyteName) {
            $analyteName = is_string($analyteCode) && ! str_starts_with($analyteCode, 'eyJ')
                ? $analyteCode
                : '—';
        }

        return $analyteName;
    }
}
