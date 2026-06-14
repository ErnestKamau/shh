<?php

namespace Tests\Feature\Sampleworkflow;

use App\Company;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use App\Models\Auth\Role;
use Tests\TestCase;

class TestRequestFormPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        Storage::fake('public');

        $this->user = User::create([
            'name' => 'Lab User',
            'email' => 'lab.user@example.test',
            'password' => bcrypt('password'),
            'password_changed_at' => now(),
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $this->user->assignRole($adminRole);

        $this->company = Company::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'AmSpec Middle East Inspection & Testing Services L.L.C',
            'active' => 1,
            'address' => 'Dubai',
            'telephone' => '+971',
            'email' => 'info@example.test',
        ]);
    }

    public function test_pdf_service_generates_and_stores_food_report(): void
    {
        $trfi = $this->createTrfi('Food', 'SMP-FOOD', [
            'customer_name' => 'ABC COMPANY',
            'sampling_date' => '2026-06-03',
            'sample_rows' => [
                [
                    'sample_no' => '1',
                    'sample_description' => 'Chicken Salad',
                    'state_of_sample' => 'Semi Solid',
                ],
            ],
        ]);

        $service = app(TestRequestFormPdfService::class);
        $url = $service->generateAndStore($trfi);

        $this->assertStringContainsString('/storage/test-request-forms/trf-', $url);
        Storage::disk('public')->assertExists($service->resolveStoragePath($trfi));
    }

    public function test_food_pdf_uses_landscape_paper(): void
    {
        $trfi = $this->createTrfi('Food', 'SMP-FOOD', [
            'customer_name' => 'ABC COMPANY',
        ]);

        $pdf = Mockery::mock();
        $domPdf = Mockery::mock();
        $pdf->shouldReceive('getDomPDF')->andReturn($domPdf);
        $domPdf->shouldReceive('set_option')->with('isHtml5ParserEnabled', true);
        $pdf->shouldReceive('loadView')->once();
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'landscape');
        $pdf->shouldReceive('output')->once()->andReturn('%PDF-1.4');

        $this->app->instance('dompdf.wrapper', $pdf);

        app(TestRequestFormPdfService::class)->generateAndStore($trfi);
    }

    public function test_preview_route_renders_html_with_customer_name(): void
    {
        $trfi = $this->createTrfi('Water', 'SMP-WTR', [
            'customer_name' => 'ABC COMPANY',
            'sample_rows' => [
                [
                    'sample_no' => '1',
                    'sample_description' => 'Tap Water',
                    'microbiology' => true,
                ],
            ],
        ]);

        $html = app(TestRequestFormPdfService::class)->buildHtml($trfi, false);

        $this->assertStringContainsString('TEST REQUEST FORM - WATER', $html);
        $this->assertStringContainsString('ABC COMPANY', $html);
        $this->assertStringContainsString('AMS/QMS/LWS/020', $html);
        $this->assertStringContainsString('TEST REQUIRMENTS', $html);
        $this->assertStringContainsString('FIELD DATA', $html);
        $this->assertStringContainsString('trf-banner-row', $html);
        $this->assertStringContainsString('trf-vtext', $html);
        $this->assertStringContainsString('trf-meta-key', $html);
    }

    public function test_food_preview_renders_collection_grid_and_checkbox_columns(): void
    {
        $trfi = $this->createTrfi('Food', 'SMP-FOOD', [
            'customer_name' => 'ABC COMPANY',
            'sampling_date' => '2026-06-03',
            'sampling_apparatus' => ['STERILE SWAB'],
            'method_of_sampling' => ['APHA'],
            'reason_of_collection' => ['CONTRACT'],
            'transport_condition' => ['CHILLER VEHICLE'],
            'sample_rows' => [
                [
                    'sample_no' => '1',
                    'sample_description' => 'Chicken Salad',
                    'sample_type' => 'Ready To Eat',
                    'sample_condition' => 'Chilled',
                    'state_of_sample' => 'Semi Solid',
                ],
            ],
        ]);

        $html = app(TestRequestFormPdfService::class)->buildHtml($trfi, false);

        $this->assertStringContainsString('TEST REQUEST FORM - FOOD', $html);
        $this->assertStringContainsString('SAMPLE COLLECTION DATA', $html);
        $this->assertStringContainsString('CUSTOMER DETAILS', $html);
        $this->assertStringContainsString('JOB NUMBER:', $html);
        $this->assertStringContainsString('SAMPLE DETAILS', $html);
        $this->assertStringContainsString('AMS/QMS/LWS/019', $html);
        $this->assertStringContainsString('FOR LAB USE ONLY', $html);
        $this->assertStringContainsString('Statement of Conformity Required in Reports:', $html);
        $this->assertStringContainsString('trf-banner-row', $html);
        $this->assertStringContainsString('trf-accent', $html);
        $this->assertStringContainsString('trf-job-label', $html);
        $this->assertStringContainsString('trf-meta-key', $html);
        $this->assertStringContainsString('trf-vtext', $html);
        $this->assertStringContainsString('SAMPLE TYPE', $html);
        $this->assertStringContainsString('SAMPLE CONDITION', $html);
        $this->assertStringContainsString('Ready To Eat', $html);
        $this->assertStringContainsString('Chilled', $html);
        $this->assertStringContainsString('trf-check-grid', $html);
        $this->assertStringContainsString('<td class="trf-center">5</td>', $html);
    }

    public function test_waste_water_preview_renders_waste_water_template(): void
    {
        $trfi = $this->createTrfi('Waste Water', 'SMP-WWTR', [
            'customer_name' => 'ABC COMPANY',
            'job_number' => 'JOB-WW-001',
            'sampling_date' => '2026-06-03',
            'sample_description' => 'Effluent discharge point',
            'sampling_source' => ['STP'],
            'field_data_requirements' => ['MICROBIOLOGY'],
        ]);

        $html = app(TestRequestFormPdfService::class)->buildHtml($trfi, false);

        $this->assertStringContainsString('TEST REQUEST FORM - WASTE WATER', $html);
        $this->assertStringContainsString('AMS/QMS/LWS/021', $html);
        $this->assertStringContainsString('FIELD DATA &amp; REQUIREMENTS', $html);
        $this->assertStringContainsString('Effluent discharge point', $html);
    }

    public function test_pdf_route_streams_document(): void
    {
        $trfi = $this->createTrfi('Food', 'SMP-FOOD', [
            'customer_name' => 'ABC COMPANY',
        ]);

        $pdf = Mockery::mock();
        $domPdf = Mockery::mock();
        $pdf->shouldReceive('getDomPDF')->andReturn($domPdf);
        $domPdf->shouldReceive('set_option')->with('isHtml5ParserEnabled', true);
        $pdf->shouldReceive('loadView')->once();
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'landscape');
        $pdf->shouldReceive('stream')->once()->andReturn(response('pdf'));

        $this->app->instance('dompdf.wrapper', $pdf);

        $response = app(TestRequestFormPdfService::class)->stream($trfi);

        $this->assertNotNull($response);
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function createTrfi(string $typeName, string $typeCode, array $formData): TestRequestFormInstance
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => $typeName,
            'code' => $typeCode,
            'active' => true,
            'company_id' => $this->company->id,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer Request Template',
            'document_code' => 'TEST/TRF',
            'description' => 'Test form',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Test instance',
            'form_number' => '2600001',
            'status' => 'received',
            'submitted_at' => now(),
            'submitted_by' => $this->user->id,
            'priority' => 'normal',
        ]);

        $trf = TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => $typeName . ' Test Request Form',
            'code' => 'TRF-TEST',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        return TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => $trf->id,
            'submission_form_instance_id' => $instance->id,
            'form_data' => $formData,
            'status' => 'submitted',
            'created_by' => $this->user->id,
        ]);
    }
}
