<?php

namespace Tests\Unit\Services\Lab;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\Lab\LabHierarchyPurgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LabHierarchyPurgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_for_company_removes_hierarchy_and_batches(): void
    {
        $companyId = (string) Str::uuid();

        $sampleType = SampleType::query()->create([
            'code' => 'PURGE-ST',
            'name' => 'Purge Sample Type',
            'company_id' => $companyId,
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'code' => 'PURGE-AT',
            'name' => 'Purge Analysis Type',
            'company_id' => $companyId,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'code' => 'PURGE-AN',
            'name' => 'Purge Analyte',
            'company_id' => $companyId,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $summary = app(LabHierarchyPurgeService::class)->purgeForCompany($companyId);

        $this->assertGreaterThan(0, $summary['sample_types']);
        $this->assertSame(0, SampleType::query()->where('company_id', $companyId)->count());
        $this->assertSame(0, AnalysisType::query()->where('company_id', $companyId)->count());
        $this->assertSame(0, Analyte::query()->where('company_id', $companyId)->count());
        $this->assertSame(0, AnalysisElements::query()->where('analysis_type_id', $analysisType->id)->count());
    }

    public function test_purge_for_company_removes_request_pipeline_records(): void
    {
        $companyId = (string) Str::uuid();

        $form = SubmissionForm::query()->create([
            'name' => 'Purge TRF Template',
            'document_code' => 'PURGE-TRF',
            'is_published' => true,
            'is_active' => true,
            'form_type' => 'template',
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'form_number' => 'PURGE-REQ-001',
            'sequence_number' => 1,
            'status' => 'submitted',
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
        ]);

        $summary = app(LabHierarchyPurgeService::class)->purgeForCompany($companyId);

        $this->assertGreaterThan(0, $summary['submission_form_instances']);
        $this->assertGreaterThan(0, $summary['sample_submission_requests']);
        $this->assertGreaterThan(0, $summary['submission_form_instances_deleted']);
        $this->assertGreaterThan(0, $summary['sample_submission_requests_deleted']);
        $this->assertSame(0, SubmissionFormInstance::query()->count());
        $this->assertSame(0, SampleSubmissionRequest::query()->count());
        $this->assertDatabaseMissing('submission_form_instances', ['id' => $instance->id]);
        $this->assertDatabaseMissing('sample_submission_requests', ['id' => $enquiry->id]);
    }
}
