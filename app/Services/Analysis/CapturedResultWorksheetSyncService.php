<?php

namespace App\Services\Analysis;

use App\AnalysisElements;
use App\AnalysisType;
use App\CapturedResult;
use Illuminate\Support\Collection;

class CapturedResultWorksheetSyncService
{
    /**
     * Copy procedure, method sequence, grouped, and hybrid worksheet configuration
     * from the matching analysis element and analysis type onto the captured result.
     */
    public function sync(
        CapturedResult $captured,
        ?AnalysisType $analysisType = null,
        ?AnalysisElements $analysisElement = null,
    ): bool {
        if ($analysisType === null && $captured->analysis_type_id) {
            $analysisType = AnalysisType::query()->find($captured->analysis_type_id);
        }

        if ($analysisElement === null && $captured->analysis_type_id && $captured->analyte_id) {
            $analysisElement = AnalysisElements::query()
                ->where('analysis_type_id', $captured->analysis_type_id)
                ->where('analyte_id', $captured->analyte_id)
                ->first();
        }

        if (! $analysisElement && ! $analysisType) {
            return false;
        }

        return $this->applyConfiguration($captured, $analysisType, $analysisElement);
    }

    /**
     * @param  Collection<int, CapturedResult>  $capturedResults
     * @param  Collection<string, AnalysisElements>  $elementsByAnalyteId  keyed by analyte_id
     * @return array{updated: int, unchanged: int}
     */
    public function syncMany(
        Collection $capturedResults,
        AnalysisType $analysisType,
        Collection $elementsByAnalyteId,
    ): array {
        $updated = 0;
        $unchanged = 0;

        foreach ($capturedResults as $captured) {
            $element = $captured->analyte_id
                ? $elementsByAnalyteId->get((string) $captured->analyte_id)
                : null;

            if ($this->sync($captured, $analysisType, $element)) {
                $captured->saveQuietly();
                $updated++;
            } else {
                $unchanged++;
            }
        }

        return ['updated' => $updated, 'unchanged' => $unchanged];
    }

    /**
     * @return Collection<string, AnalysisElements> keyed by analyte_id
     */
    public function loadElementsByAnalyteId(string $analysisTypeId): Collection
    {
        return AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->get()
            ->keyBy(fn (AnalysisElements $element) => (string) $element->analyte_id);
    }

    protected function applyConfiguration(
        CapturedResult $captured,
        ?AnalysisType $analysisType,
        ?AnalysisElements $analysisElement,
    ): bool {
        $changed = false;

        if ($analysisElement) {
            if ($captured->analysis_element_id !== $analysisElement->id) {
                $captured->analysis_element_id = $analysisElement->id;
                $changed = true;
            }

            if ($analysisElement->result_is_calculated && $analysisElement->formular_id) {
                if ($captured->formular_id !== $analysisElement->formular_id) {
                    $captured->formular_id = $analysisElement->formular_id;
                    $changed = true;
                }
            }

            if ($analysisElement->has_method_sequence && $analysisElement->method_sequence_id) {
                if ($captured->method_sequence_id !== $analysisElement->method_sequence_id) {
                    $captured->method_sequence_id = $analysisElement->method_sequence_id;
                    $changed = true;
                }
            }

            if ($analysisElement->stage_header_id) {
                if ($captured->stage_header_id !== $analysisElement->stage_header_id) {
                    $captured->stage_header_id = $analysisElement->stage_header_id;
                    $changed = true;
                }
            }
        }

        $procedureWorksheetId = $analysisElement?->procedure_worksheet_id
            ?? $analysisType?->procedure_worksheet_id;

        if ($procedureWorksheetId) {
            if ($captured->procedure_worksheet_id !== $procedureWorksheetId) {
                $captured->procedure_worksheet_id = $procedureWorksheetId;
                $changed = true;
            }

            if (! $captured->has_procedure_worksheet) {
                $captured->has_procedure_worksheet = true;
                $changed = true;
            }

            if (is_null($captured->result) || $captured->result === '') {
                $captured->result = 'No attachment';
                $changed = true;
            }
        }

        if ($analysisType?->grouped_worksheet_holder_id) {
            if ($captured->grouped_worksheet_holder_id !== $analysisType->grouped_worksheet_holder_id) {
                $captured->grouped_worksheet_holder_id = $analysisType->grouped_worksheet_holder_id;
                $changed = true;
            }

            if (! $captured->has_grouped_worksheet) {
                $captured->has_grouped_worksheet = true;
                $changed = true;
            }
        }

        if ($analysisType?->hybrid_worksheet_id) {
            if ($captured->hybrid_worksheet_id !== $analysisType->hybrid_worksheet_id) {
                $captured->hybrid_worksheet_id = $analysisType->hybrid_worksheet_id;
                $changed = true;
            }

            if (! $captured->has_hybrid_worksheet) {
                $captured->has_hybrid_worksheet = true;
                $changed = true;
            }
        }

        if ($analysisElement?->log_entry_worksheet_id) {
            if ($captured->log_entry_worksheet_id !== $analysisElement->log_entry_worksheet_id) {
                $captured->log_entry_worksheet_id = $analysisElement->log_entry_worksheet_id;
                $changed = true;
            }

            if (! $captured->has_log_entry_worksheet) {
                $captured->has_log_entry_worksheet = true;
                $changed = true;
            }
        }

        return $changed;
    }
}
