<?php

namespace Tests\Feature\Sampleworkflow;

use App\Livewire\Sampleworkflow\SampleRejectionWizard;
use App\Models\CRM\CRMCustomer;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\System\SystemConfiguration;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SampleRejectionWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_requires_reason_explanations(): void
    {
        $user = User::query()->create([
            'name' => 'Reviewer',
            'email' => 'reviewer.wizard@example.test',
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

        $customer = CRMCustomer::query()->create([
            'name' => 'Wizard Customer',
            'code' => 'WC001',
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
            'title' => 'Request W1',
        ]);

        Livewire::actingAs($user)
            ->test(SampleRejectionWizard::class)
            ->call('openWizard', submissionFormInstanceId: (string) $instance->id)
            ->assertSet('showModal', true)
            ->set('selectedReasonKeys.leaking', true)
            ->set('reasonExplanations.leaking', '')
            ->call('submitRejection')
            ->assertHasErrors();
    }
}
