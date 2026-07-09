<?php

namespace App\Services\Lab;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\SampleDetails;
use App\SampleHeader;
use App\SampleType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LabHierarchyPurgeService
{
    /**
     * @return array<string, int>
     */
    public function purgeForCompany(string $companyId): array
    {
        $summary = [];

        DB::transaction(function () use ($companyId, &$summary): void {
            $sampleTypeIds = SampleType::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $analysisTypeIds = AnalysisType::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $analyteIds = Analyte::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $analysisElementIds = $analysisTypeIds->isEmpty()
                ? collect()
                : AnalysisElements::query()
                    ->whereIn('analysis_type_id', $analysisTypeIds)
                    ->pluck('id');

            $sampleHeaderIds = $sampleTypeIds->isEmpty()
                ? collect()
                : SampleHeader::query()
                    ->whereIn('sample_type_id', $sampleTypeIds)
                    ->pluck('id');

            $sampleDetailIds = $sampleHeaderIds->isEmpty()
                ? collect()
                : SampleDetails::query()
                    ->whereIn('sample_header_id', $sampleHeaderIds)
                    ->pluck('id');

            $summary['sample_types'] = $sampleTypeIds->count();
            $summary['analysis_types'] = $analysisTypeIds->count();
            $summary['analytes'] = $analyteIds->count();
            $summary['analysis_elements'] = $analysisElementIds->count();
            $summary['sample_headers'] = $sampleHeaderIds->count();
            $summary['sample_details'] = $sampleDetailIds->count();

            $this->purgeBatchTrees($sampleHeaderIds, $sampleDetailIds, $summary);
            $this->purgeConfigurationData(
                $companyId,
                $sampleTypeIds,
                $analysisTypeIds,
                $analyteIds,
                $analysisElementIds,
                $summary
            );
        });

        return $summary;
    }

    /**
     * @param  Collection<int, string>  $sampleHeaderIds
     * @param  Collection<int, string>  $sampleDetailIds
     * @param  array<string, int>  $summary
     */
    private function purgeBatchTrees(Collection $sampleHeaderIds, Collection $sampleDetailIds, array &$summary): void
    {
        if ($sampleHeaderIds->isEmpty()) {
            return;
        }

        $headerIdList = $sampleHeaderIds->all();
        $detailIdList = $sampleDetailIds->all();

        if ($detailIdList !== []) {
            $summary['captured_results'] = $this->deleteWhereIn('captured_results', 'sample_detail_id', $detailIdList);
            $summary['results'] = $this->deleteWhereIn('results', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('captured_view', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_analysis_type_relation', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_analysis_dates', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_captured_worksheet_formulas', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('method_sequence_run_samples', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('qc_results', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_progress', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('ser_header_worksheet_sample_relations', 'sample_detail_id', $detailIdList);
        }

        $this->deleteWhereIn('captured_results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('captured_view', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('sample_analysis_type_relation', 'batch_id', $headerIdList);
        $this->deleteWhereIn('sample_analysis_dates', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('sample_captured_worksheet_formulas', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('method_sequence_runs', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('qc_results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('chain_of_custodies', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('batch_attachments', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_ammendments', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_labsection_approval', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_notifications', 'batch_id', $headerIdList);
        $this->deleteWhereIn('import_lab_results', 'batch_id', $headerIdList);
        $this->deleteWhereIn('invoice_details', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('report_header_details', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('supporting_document_instances', 'sample_header_id', $headerIdList);

        AnalysisAcceptanceForm::query()
            ->whereIn('sample_header_id', $headerIdList)
            ->each(function (AnalysisAcceptanceForm $form): void {
                $form->lines()->delete();
                $form->delete();
            });

        if (Schema::hasTable('sample_submission_requests') && Schema::hasColumn('sample_submission_requests', 'sample_header_id')) {
            DB::table('sample_submission_requests')
                ->whereIn('sample_header_id', $headerIdList)
                ->update(['sample_header_id' => null]);
        }

        if (Schema::hasTable('submission_form_instances') && Schema::hasColumn('submission_form_instances', 'sample_header_id')) {
            DB::table('submission_form_instances')
                ->whereIn('sample_header_id', $headerIdList)
                ->update(['sample_header_id' => null]);
        }

        if ($detailIdList !== []) {
            SampleDetails::query()->whereIn('id', $detailIdList)->delete();
        }

        $summary['sample_headers_deleted'] = SampleHeader::query()->whereIn('id', $headerIdList)->delete();
    }

    /**
     * @param  Collection<int, string>  $sampleTypeIds
     * @param  Collection<int, string>  $analysisTypeIds
     * @param  Collection<int, string>  $analyteIds
     * @param  Collection<int, string>  $analysisElementIds
     * @param  array<string, int>  $summary
     */
    private function purgeConfigurationData(
        string $companyId,
        Collection $sampleTypeIds,
        Collection $analysisTypeIds,
        Collection $analyteIds,
        Collection $analysisElementIds,
        array &$summary,
    ): void {
        if ($sampleTypeIds->isNotEmpty()) {
            $sampleTypeIdList = $sampleTypeIds->all();
            $summary['pricelist_items'] = $this->deleteWhereIn('pricelist_items', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('sample_conditions', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('sample_to_sample_analysis_stages', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('sampletype_sample_point_relation', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('sampletype_area_relation', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('qc_processed_result', 'sample_type_id', $sampleTypeIdList);
        }

        if ($analysisElementIds->isNotEmpty()) {
            $this->deleteWhereIn('pricelist_items', 'analysis_element_id', $analysisElementIds->all());
        }

        if ($analysisTypeIds->isNotEmpty()) {
            $analysisTypeIdList = $analysisTypeIds->all();
            $summary['analysis_guides'] = $this->deleteWhereIn('analysis_guides', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('qc_processed_result', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('qc_results', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('analysis_type_invoicable_item', 'analysis_type_id', $analysisTypeIdList);

            if (Schema::hasTable('analysis_type_lab_relation')) {
                $this->deleteWhereIn('analysis_type_lab_relation', 'analysis_type_id', $analysisTypeIdList);
            }
        }

        if ($analyteIds->isNotEmpty()) {
            $analyteIdList = $analyteIds->all();
            $summary['uncertainty_budgets'] = $this->deleteWhereIn('uncertainty_budgets', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('analysis_method_elements', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('qc_processed_result', 'analyte_id', $analyteIdList);
        }

        if ($analysisTypeIds->isNotEmpty()) {
            $summary['analysis_elements_deleted'] = AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds->all())
                ->delete();
        }

        if ($analysisTypeIds->isNotEmpty()) {
            $summary['analysis_types_deleted'] = AnalysisType::query()
                ->where('company_id', $companyId)
                ->delete();
        }

        if ($sampleTypeIds->isNotEmpty()) {
            $summary['sample_types_deleted'] = SampleType::query()
                ->where('company_id', $companyId)
                ->delete();
        }

        if ($analyteIds->isNotEmpty()) {
            $summary['analytes_deleted'] = Analyte::query()
                ->where('company_id', $companyId)
                ->delete();
        }
    }

    /**
     * @param  array<int, string>  $ids
     */
    private function deleteWhereIn(string $table, string $column, array $ids): int
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)->whereIn($column, $ids)->delete();
    }
}
