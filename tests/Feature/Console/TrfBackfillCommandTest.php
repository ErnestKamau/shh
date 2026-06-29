<?php

namespace Tests\Feature\Console;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrfBackfillCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('TRF layer deprecated — see docs/deprecation/TRF_LAYER_MANIFEST.md');
    }

    public function test_backfill_links_ssr_to_existing_trfi(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        TestRequestForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'TRF Water',
            'code' => 'TRF-WATER',
            'sample_type_id' => $sampleType->id,
            'form_fields' => ['sections' => []],
            'is_active' => true,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'form_type' => 'template',
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'form_number' => 'TRFW001/26',
            'sequence_number' => 1,
            'status' => 'submitted',
        ]);

        $trfi = TestRequestFormInstance::query()->create([
            'id' => (string) Str::uuid7(),
            'test_request_form_id' => TestRequestForm::query()->first()->id,
            'submission_form_instance_id' => $instance->id,
            'form_data' => ['customer_name' => 'Legacy'],
            'status' => TestRequestFormInstance::STATUS_SUBMITTED,
        ]);

        $ssr = SampleSubmissionRequest::query()->create([
            'submission_form_instance_id' => $instance->id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
        ]);

        $this->artisan('trf:backfill-canonical-links')
            ->assertSuccessful();

        $trfi->refresh();
        $ssr->refresh();

        $this->assertSame('TRFW001/26', $trfi->form_number);
        $this->assertSame($trfi->id, $ssr->test_request_form_instance_id);
        $this->assertSame($ssr->id, $trfi->sample_submission_request_id);
    }

    public function test_dry_run_is_idempotent_and_reports_counts(): void
    {
        $this->artisan('trf:backfill-canonical-links', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutput('Dry run complete.');
    }
}
