<?php

namespace App\Services\Lab;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
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

            $this->purgeHierarchyGraph(
                $companyId,
                $sampleTypeIds,
                $analysisTypeIds,
                $analyteIds,
                $analysisElementIds,
                $summary,
                includeRequestPipeline: true,
            );
        });

        return $summary;
    }

    /**
     * Replace Sample Types & Analysis Types import: delete Sample Types, Analysis Types,
     * and Analysis Parameters under them. Analytes and Labs are kept.
     *
     * Sample headers that reference the company's sample types are removed (sample_type_id
     * is NOT NULL), along with their batch/result trees. The wider request pipeline is not wiped.
     *
     * @return array<string, int>
     */
    public function purgeAnalysisTypesForCompany(string $companyId): array
    {
        $summary = [];

        DB::transaction(function () use ($companyId, &$summary): void {
            $sampleTypeIds = SampleType::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $analysisTypeIds = AnalysisType::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $analysisElementIds = $analysisTypeIds->isEmpty()
                ? collect()
                : AnalysisElements::query()
                    ->whereIn('analysis_type_id', $analysisTypeIds)
                    ->pluck('id');

            // Keep analytes; only purge sample/analysis hierarchy for this company.
            $this->purgeHierarchyGraph(
                $companyId,
                $sampleTypeIds,
                $analysisTypeIds,
                collect(),
                $analysisElementIds,
                $summary,
                includeRequestPipeline: false,
            );
        });

        return $summary;
    }

    /**
     * Replace Analytes only: remove analytes and any Analysis Elements / standard
     * mappings that point at them so no orphan parameter rows remain.
     *
     * @return array<string, int>
     */
    public function purgeAnalytesForCompany(string $companyId): array
    {
        $summary = [];

        DB::transaction(function () use ($companyId, &$summary): void {
            $analyteIds = Analyte::query()
                ->where('company_id', $companyId)
                ->pluck('id');

            $summary['analytes'] = $analyteIds->count();

            if ($analyteIds->isEmpty()) {
                return;
            }

            $analyteIdList = $analyteIds->all();

            $analysisElementIds = AnalysisElements::query()
                ->whereIn('analyte_id', $analyteIdList)
                ->pluck('id');

            $summary['analysis_elements'] = $analysisElementIds->count();

            if ($analysisElementIds->isNotEmpty()) {
                $this->deleteWhereIn('pricelist_items', 'analysis_element_id', $analysisElementIds->all());
                $summary['analysis_elements_deleted'] = AnalysisElements::query()
                    ->whereIn('id', $analysisElementIds->all())
                    ->delete();
            }

            $this->purgeAnalyteRestrictedDependents($analyteIdList, $summary);

            $summary['uncertainty_budgets'] = $this->deleteWhereIn('uncertainty_budgets', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('analysis_method_elements', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('qc_processed_result', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('standards_analytes', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('captured_results', 'analyte_id', $analyteIdList);
            $this->deleteWhereIn('results', 'analyte_id', $analyteIdList);

            $summary['analytes_deleted'] = Analyte::query()
                ->where('company_id', $companyId)
                ->delete();
        });

        return $summary;
    }

    /**
     * @param  Collection<int, string>  $sampleTypeIds
     * @param  Collection<int, string>  $analysisTypeIds
     * @param  Collection<int, string>  $analyteIds
     * @param  Collection<int, string>  $analysisElementIds
     * @param  array<string, int>  $summary
     */
    private function purgeHierarchyGraph(
        string $companyId,
        Collection $sampleTypeIds,
        Collection $analysisTypeIds,
        Collection $analyteIds,
        Collection $analysisElementIds,
        array &$summary,
        bool $includeRequestPipeline = true,
    ): void {
        $submissionFormInstanceIds = $includeRequestPipeline
            ? $this->resolveSubmissionFormInstanceIds()
            : collect();
        $submissionRequestIds = $includeRequestPipeline
            ? $this->resolveSubmissionRequestIds($submissionFormInstanceIds)
            : collect();

        $sampleHeaderIds = $this->resolveSampleHeaderIds(
            $sampleTypeIds,
            $submissionFormInstanceIds,
            $submissionRequestIds
        );

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
        $summary['submission_form_instances'] = $submissionFormInstanceIds->count();
        $summary['sample_submission_requests'] = $submissionRequestIds->count();

        $this->purgeBatchTrees($sampleHeaderIds, $sampleDetailIds, $summary);

        if ($includeRequestPipeline) {
            $this->purgeRequestPipeline($submissionFormInstanceIds, $submissionRequestIds, $summary);
        }

        $this->purgeConfigurationData(
            $companyId,
            $sampleTypeIds,
            $analysisTypeIds,
            $analyteIds,
            $analysisElementIds,
            $summary
        );
    }

    /**
     * Request/enquiry tables do not carry company_id; the replace-existing import is a
     * destructive reset for the deployment's workflow pipeline, matching seeder behaviour.
     *
     * @return Collection<int, string>
     */
    private function resolveSubmissionFormInstanceIds(): Collection
    {
        if (! Schema::hasTable('submission_form_instances')) {
            return collect();
        }

        return SubmissionFormInstance::query()->pluck('id');
    }

    /**
     * @param  Collection<int, string>  $submissionFormInstanceIds
     * @return Collection<int, string>
     */
    private function resolveSubmissionRequestIds(Collection $submissionFormInstanceIds): Collection
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return collect();
        }

        $requestIds = SampleSubmissionRequest::query()->pluck('id');

        if ($submissionFormInstanceIds->isEmpty()
            || ! Schema::hasColumn('sample_submission_requests', 'submission_form_instance_id')) {
            return $requestIds->unique()->values();
        }

        $linkedRequestIds = SampleSubmissionRequest::query()
            ->whereIn('submission_form_instance_id', $submissionFormInstanceIds)
            ->pluck('id');

        return $requestIds->merge($linkedRequestIds)->unique()->values();
    }

    /**
     * @param  Collection<int, string>  $sampleTypeIds
     * @param  Collection<int, string>  $submissionFormInstanceIds
     * @param  Collection<int, string>  $submissionRequestIds
     * @return Collection<int, string>
     */
    private function resolveSampleHeaderIds(
        Collection $sampleTypeIds,
        Collection $submissionFormInstanceIds,
        Collection $submissionRequestIds,
    ): Collection {
        $headerIds = collect();

        if ($sampleTypeIds->isNotEmpty()) {
            $headerIds = $headerIds->merge(
                SampleHeader::query()
                    ->whereIn('sample_type_id', $sampleTypeIds)
                    ->pluck('id')
            );
        }

        if ($submissionFormInstanceIds->isNotEmpty()
            && Schema::hasColumn('sample_headers', 'submission_form_instance_id')) {
            $headerIds = $headerIds->merge(
                SampleHeader::query()
                    ->whereIn('submission_form_instance_id', $submissionFormInstanceIds)
                    ->pluck('id')
            );
        }

        if ($submissionRequestIds->isNotEmpty()) {
            if (Schema::hasColumn('sample_headers', 'sample_submission_request_id')) {
                $headerIds = $headerIds->merge(
                    SampleHeader::query()
                        ->whereIn('sample_submission_request_id', $submissionRequestIds)
                        ->pluck('id')
                );
            }

            if (Schema::hasColumn('sample_submission_requests', 'sample_header_id')) {
                $headerIds = $headerIds->merge(
                    SampleSubmissionRequest::query()
                        ->whereIn('id', $submissionRequestIds)
                        ->whereNotNull('sample_header_id')
                        ->pluck('sample_header_id')
                );
            }
        }

        return $headerIds->filter()->unique()->values();
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

        if ($detailIdList !== []) {
            SampleDetails::query()->whereIn('id', $detailIdList)->delete();
        }

        $summary['sample_headers_deleted'] = SampleHeader::query()->whereIn('id', $headerIdList)->delete();
    }

    /**
     * @param  Collection<int, string>  $submissionFormInstanceIds
     * @param  Collection<int, string>  $submissionRequestIds
     * @param  array<string, int>  $summary
     */
    private function purgeRequestPipeline(
        Collection $submissionFormInstanceIds,
        Collection $submissionRequestIds,
        array &$summary,
    ): void {
        $instanceIdList = $this->expandSubmissionFormInstanceIds($submissionFormInstanceIds);
        $requestIdList = $submissionRequestIds->all();

        if ($instanceIdList === [] && $requestIdList === []) {
            return;
        }

        $this->purgeAcceptanceFormsForRequests($instanceIdList, $requestIdList, $summary);
        $this->purgeRejectionLogsForRequests($instanceIdList, $requestIdList, $summary);
        $this->purgeRequestWorkflowForms($instanceIdList, $requestIdList, $summary);
        $this->purgeInterzoneTransfersForInstances($instanceIdList, $summary);
        $this->purgeQuotationsForRequests($requestIdList, $summary);
        $this->purgeSupportingDocumentsForRequests($requestIdList, $summary);
        $this->purgeEnquiryChildTables($requestIdList, $summary);
        $this->purgeSubmissionFormInstanceChildTables($instanceIdList, $summary);

        if ($requestIdList !== []) {
            $summary['sample_submission_requests_deleted'] = SampleSubmissionRequest::query()
                ->whereIn('id', $requestIdList)
                ->delete();
        }

        if ($instanceIdList !== []) {
            $summary['submission_form_instances_deleted'] = SubmissionFormInstance::query()
                ->whereIn('id', $instanceIdList)
                ->delete();
        }
    }

    /**
     * @param  Collection<int, string>  $submissionFormInstanceIds
     * @return array<int, string>
     */
    private function expandSubmissionFormInstanceIds(Collection $submissionFormInstanceIds): array
    {
        $instanceIdList = $submissionFormInstanceIds->all();

        if ($instanceIdList === []
            || ! Schema::hasTable('submission_form_instances')
            || ! Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
            return $instanceIdList;
        }

        $childIds = DB::table('submission_form_instances')
            ->whereIn('portal_request_id', $instanceIdList)
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($instanceIdList, $childIds)));
    }

    /**
     * @param  array<int, string>  $instanceIdList
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeAcceptanceFormsForRequests(array $instanceIdList, array $requestIdList, array &$summary): void
    {
        if ($instanceIdList === [] && $requestIdList === []) {
            return;
        }

        $query = AnalysisAcceptanceForm::query()->where(function ($builder) use ($instanceIdList, $requestIdList): void {
            $hasFilter = false;

            if ($instanceIdList !== []) {
                $builder->whereIn('submission_form_instance_id', $instanceIdList);
                $hasFilter = true;
            }

            if ($requestIdList !== []) {
                if ($hasFilter) {
                    $builder->orWhereIn('sample_submission_request_id', $requestIdList);
                } else {
                    $builder->whereIn('sample_submission_request_id', $requestIdList);
                }
            }
        });

        $summary['analysis_acceptance_forms'] = (clone $query)->count();

        $query->each(function (AnalysisAcceptanceForm $form): void {
            $form->lines()->delete();
            $form->delete();
        });
    }

    /**
     * @param  array<int, string>  $instanceIdList
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeRejectionLogsForRequests(array $instanceIdList, array $requestIdList, array &$summary): void
    {
        if (! Schema::hasTable('sample_rejection_logs')) {
            return;
        }

        $deleted = 0;

        if ($instanceIdList !== [] && Schema::hasColumn('sample_rejection_logs', 'submission_form_instance_id')) {
            $deleted += $this->deleteWhereIn('sample_rejection_logs', 'submission_form_instance_id', $instanceIdList);
        }

        if ($requestIdList !== [] && Schema::hasColumn('sample_rejection_logs', 'sample_submission_request_id')) {
            $deleted += $this->deleteWhereIn('sample_rejection_logs', 'sample_submission_request_id', $requestIdList);
        }

        if ($deleted > 0) {
            $summary['sample_rejection_logs'] = $deleted;
        }
    }

    /**
     * @param  array<int, string>  $instanceIdList
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeRequestWorkflowForms(array $instanceIdList, array $requestIdList, array &$summary): void
    {
        if (! Schema::hasTable('request_workflow_forms')) {
            return;
        }

        $deleted = 0;

        if ($instanceIdList !== [] && Schema::hasColumn('request_workflow_forms', 'submission_form_instance_id')) {
            $deleted += $this->deleteWhereIn('request_workflow_forms', 'submission_form_instance_id', $instanceIdList);
        }

        if ($requestIdList !== [] && Schema::hasColumn('request_workflow_forms', 'sample_submission_request_id')) {
            $deleted += $this->deleteWhereIn('request_workflow_forms', 'sample_submission_request_id', $requestIdList);
        }

        if ($deleted > 0) {
            $summary['request_workflow_forms'] = $deleted;
        }
    }

    /**
     * @param  array<int, string>  $instanceIdList
     * @param  array<string, int>  $summary
     */
    private function purgeInterzoneTransfersForInstances(array $instanceIdList, array &$summary): void
    {
        if ($instanceIdList === []
            || ! Schema::hasTable('interzone_transfers')
            || ! Schema::hasColumn('interzone_transfers', 'submission_form_instance_id')) {
            return;
        }

        $transferIds = DB::table('interzone_transfers')
            ->whereIn('submission_form_instance_id', $instanceIdList)
            ->pluck('id')
            ->all();

        if ($transferIds === []) {
            return;
        }

        if (Schema::hasTable('interzone_transfer_samples')) {
            $summary['interzone_transfer_samples'] = $this->deleteWhereIn(
                'interzone_transfer_samples',
                'interzone_transfer_id',
                $transferIds
            );
        }

        $summary['interzone_transfers'] = $this->deleteWhereIn('interzone_transfers', 'id', $transferIds);
    }

    /**
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeQuotationsForRequests(array $requestIdList, array &$summary): void
    {
        if ($requestIdList === []
            || ! Schema::hasTable('quotation_headers')
            || ! Schema::hasColumn('quotation_headers', 'sample_submission_request_id')) {
            return;
        }

        $quotationHeaderIds = DB::table('quotation_headers')
            ->whereIn('sample_submission_request_id', $requestIdList)
            ->pluck('id')
            ->all();

        if ($quotationHeaderIds === []) {
            return;
        }

        if (Schema::hasTable('quotation_details')) {
            $summary['quotation_details'] = $this->deleteWhereIn(
                'quotation_details',
                'quotation_header_id',
                $quotationHeaderIds
            );
        }

        $summary['quotation_headers'] = $this->deleteWhereIn('quotation_headers', 'id', $quotationHeaderIds);
    }

    /**
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeSupportingDocumentsForRequests(array $requestIdList, array &$summary): void
    {
        if ($requestIdList === []
            || ! Schema::hasTable('supporting_document_instances')
            || ! Schema::hasColumn('supporting_document_instances', 'sample_submission_request_id')) {
            return;
        }

        $supportingDocumentIds = DB::table('supporting_document_instances')
            ->whereIn('sample_submission_request_id', $requestIdList)
            ->pluck('id')
            ->all();

        if ($supportingDocumentIds === []) {
            return;
        }

        if (Schema::hasTable('supporting_document_instance_values')) {
            $summary['supporting_document_instance_values'] = $this->deleteWhereIn(
                'supporting_document_instance_values',
                'supporting_document_instance_id',
                $supportingDocumentIds
            );
        }

        $summary['supporting_document_instances'] = $this->deleteWhereIn(
            'supporting_document_instances',
            'id',
            $supportingDocumentIds
        );
    }

    /**
     * @param  array<int, string>  $requestIdList
     * @param  array<string, int>  $summary
     */
    private function purgeEnquiryChildTables(array $requestIdList, array &$summary): void
    {
        if ($requestIdList === []) {
            return;
        }

        foreach ([
            'sample_submission_request_requested_analyses',
            'sample_submission_request_exhibits',
            'sample_submission_request_suspects',
            'sample_submission_request_supporting_document_templates',
        ] as $table) {
            $deleted = $this->deleteWhereIn($table, 'sample_submission_request_id', $requestIdList);

            if ($deleted > 0) {
                $summary[$table] = $deleted;
            }
        }
    }

    /**
     * @param  array<int, string>  $instanceIdList
     * @param  array<string, int>  $summary
     */
    private function purgeSubmissionFormInstanceChildTables(array $instanceIdList, array &$summary): void
    {
        if ($instanceIdList === []) {
            return;
        }

        foreach ([
            'submission_form_instance_values',
            'submission_form_instance_notes',
            'submission_form_instance_attachments',
            'submission_form_instance_intrays',
        ] as $table) {
            $deleted = $this->deleteWhereIn($table, 'submission_form_instance_id', $instanceIdList);

            if ($deleted > 0) {
                $summary[$table] = $deleted;
            }
        }
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
            $this->deleteWhereIn('analysis_acceptance_form_lines', 'sample_type_id', $sampleTypeIdList);
            $this->deleteWhereIn('sample_submission_request_requested_analyses', 'sample_type_id', $sampleTypeIdList);

            // stage_headers.sample_type_id is ON DELETE NO ACTION (nullable).
            if (Schema::hasTable('stage_headers') && Schema::hasColumn('stage_headers', 'sample_type_id')) {
                DB::table('stage_headers')
                    ->whereIn('sample_type_id', $sampleTypeIdList)
                    ->update(['sample_type_id' => null]);
            }
        }

        if ($analysisElementIds->isNotEmpty()) {
            $elementIdList = $analysisElementIds->all();
            $this->deleteWhereIn('pricelist_item_elements', 'analysis_element_id', $elementIdList);
            $this->deleteWhereIn('pricelist_items', 'analysis_element_id', $elementIdList);
            $this->deleteWhereIn('subcontracting_dispatch_assignments', 'analysis_element_id', $elementIdList);
            $this->deleteWhereIn('sample_submission_request_requested_analyses', 'analysis_element_id', $elementIdList);
            $this->deleteWhereIn('analysis_acceptance_form_lines', 'analysis_element_id', $elementIdList);
        }

        if ($analysisTypeIds->isNotEmpty()) {
            $analysisTypeIdList = $analysisTypeIds->all();
            $summary['analysis_guides'] = $this->deleteWhereIn('analysis_guides', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('qc_processed_result', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('qc_results', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('analysis_type_invoicable_item', 'analysis_type_id', $analysisTypeIdList);
            $this->deleteWhereIn('sample_analysis_type_relation', 'analysis_type_id', $analysisTypeIdList);

            if (Schema::hasTable('analysis_type_lab_relation')) {
                $this->deleteWhereIn('analysis_type_lab_relation', 'analysis_type_id', $analysisTypeIdList);
            }
        }

        if ($analyteIds->isNotEmpty()) {
            $analyteIdList = $analyteIds->all();
            $this->purgeAnalyteRestrictedDependents($analyteIdList, $summary);
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
     * Clear dependents whose analyte FKs use ON DELETE NO ACTION (stage_headers,
     * sample_progress) so analyte rows can be removed safely.
     *
     * @param  array<int, string>  $analyteIdList
     * @param  array<string, int>  $summary
     */
    private function purgeAnalyteRestrictedDependents(array $analyteIdList, array &$summary): void
    {
        if ($analyteIdList === []) {
            return;
        }

        $stageHeaderIds = [];

        if (Schema::hasTable('stage_headers') && Schema::hasColumn('stage_headers', 'analyte_id')) {
            $stageHeaderIds = DB::table('stage_headers')
                ->whereIn('analyte_id', $analyteIdList)
                ->pluck('id')
                ->all();
        }

        if ($stageHeaderIds !== []) {
            $this->deleteWhereIn('sample_progress', 'stage_header_id', $stageHeaderIds);
        }

        $this->deleteWhereIn('sample_progress', 'analyte_id', $analyteIdList);
        $summary['stage_headers'] = $this->deleteWhereIn('stage_headers', 'analyte_id', $analyteIdList);
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
