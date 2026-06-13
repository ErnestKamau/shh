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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TestRequestFormPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        Storage::fake('public');

        $this->user = User::create([
            'name' => 'Lab User',
            'email' => 'lab.user@example.test',
            'password' => bcrypt('password'),
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $this->user->assignRole($adminRole);

        Company::query()->create([
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

        $response = $this->actingAs($this->user)
            ->get(route('test-request-form.preview', $trfi->id));

        $response->assertOk();
        $response->assertSee('TEST REQUEST FORM - WATER');
        $response->assertSee('ABC COMPANY');
        $response->assertSee('AMS/QMS/LWS/020');
        $response->assertDontSee('trf-hex');
        $response->assertSee('FIELD DATA');
        $response->assertSee('TEST REQUIREMENTS');
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

        $response = $this->actingAs($this->user)
            ->get(route('test-request-form.preview', $trfi->id));

        $response->assertOk();
        $response->assertSee('TEST REQUEST FORM - FOOD');
        $response->assertSee('Sample Collection Data');
        $response->assertSee('AMS/QMS/LWS/019');
        $response->assertDontSee('trf-hex');
        $response->assertSee('Ready To Eat');
        $response->assertSee('Chilled');
    }

    public function test_pdf_route_streams_document(): void
    {
        $trfi = $this->createTrfi('Food', 'SMP-FOOD', [
            'customer_name' => 'ABC COMPANY',
        ]);

        app(TestRequestFormPdfService::class)->generateAndStore($trfi);

        $response = $this->actingAs($this->user)
            ->get(route('test-request-form.pdf', $trfi->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
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
