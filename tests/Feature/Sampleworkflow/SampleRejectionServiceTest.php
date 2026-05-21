<?php

namespace Tests\Feature\Sampleworkflow;

use App\Jobs\Sampleworkflow\SendSampleRejectionNotifications;
use App\Models\CRM\CRMCustomer;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\System\SystemConfiguration;
use App\Services\Sampleworkflow\SampleRejectionService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SampleRejectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reviewer = User::query()->create([
            'name' => 'Reviewer',
            'email' => 'reviewer@example.test',
            'password' => Hash::make('password'),
            'active' => 1,
            'is_client' => 0,
        ]);

        SystemConfiguration::query()->create([
            'key' => 'sample_rejection_reasons',
            'value' => json_encode([
                ['key' => 'leaking', 'label' => 'Sample was leaking'],
            ]),
            'status' => true,
        ]);
    }

    public function test_reject_creates_log_updates_instance_and_dispatches_job(): void
    {
        Bus::fake();

        $customer = CRMCustomer::query()->create([
            'name' => 'Reject Customer',
            'code' => 'RC001',
            'email' => 'customer@example.test',
        ]);

        $form = SubmissionForm::query()->create([
            'name' => 'Portal Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'title' => 'Request 1',
            'form_number' => 'REQ-001',
        ]);

        $log = app(SampleRejectionService::class)->rejectFromRequestReview(
            (string) $instance->id,
            null,
            [
                [
                    'key' => 'leaking',
                    'label' => 'Sample was leaking',
                    'explanation' => 'Seal was broken on arrival.',
                ],
            ],
            (string) $this->reviewer->id,
        );

        $instance->refresh();

        $this->assertSame('rejected', $instance->status);
        $this->assertNotNull($instance->reviewed_at);
        $this->assertDatabaseHas('sample_rejection_logs', [
            'id' => $log->id,
            'submission_form_instance_id' => $instance->id,
            'client_name' => 'Reject Customer',
        ]);
        $this->assertCount(1, $log->reasons);
        $this->assertSame('Seal was broken on arrival.', $log->reasons[0]['explanation']);

        Bus::assertDispatched(SendSampleRejectionNotifications::class, function ($job) use ($log) {
            return $job->sampleRejectionLogId === $log->id;
        });
    }

    public function test_reject_requires_at_least_one_reason(): void
    {
        $customer = CRMCustomer::query()->create([
            'name' => 'Reject Customer 2',
            'code' => 'RC002',
        ]);

        $form = SubmissionForm::query()->create([
            'name' => 'Portal Form',
            'form_type' => 'template',
            'active' => true,
        ]);

        $instance = SubmissionFormInstance::query()->create([
            'submission_form_id' => $form->id,
            'crm_customer_id' => $customer->id,
            'status' => 'in_review',
            'title' => 'Request 2',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(SampleRejectionService::class)->rejectFromRequestReview(
            (string) $instance->id,
            null,
            [],
            (string) $this->reviewer->id,
        );
    }
}
