<?php

namespace Tests\Feature;

use App\Livewire\Crm\Customer\Tabs\CustomerFeedbacksTab;
use App\Livewire\Crm\Feedback\FeedbackList;
use App\Mail\LowScoreAlert;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\EvaluationMetric;
use App\Models\CRM\FeedbackRating;
use App\Services\CRM\FeedbackCorrectiveActionService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackCorrectiveActionFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected CRMCustomer $customer;
    protected int $contactId;
    protected FeedbackCorrectiveActionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('companies')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Test Lab',
                'logo' => '/images/no-logo.png',
                'location' => 'Nairobi',
                'address' => 'Test Address',
                'country_id' => 1,
                'website' => 'https://example.com',
                'active' => 1,
                'show_on_reports' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->user = User::factory()->create([
            'company_id' => 1,
            'active' => 1,
        ]);

        $this->customer = CRMCustomer::create([
            'name' => 'Feedback CA Customer',
            'code' => 'FCA001',
            'company_id' => 1,
            'active' => 1,
            'email' => 'feedback-ca@example.com',
            'telephone1' => '1234567890',
            'telephone2' => '',
            'website' => 'https://example.com',
            'country_id' => 1,
            'postal_address' => 'P.O. Box 1',
            'physical_address' => 'Nairobi',
            'fax' => null,
        ]);

        $this->contactId = (int) DB::table('crm_customer_contacts')->insertGetId([
            'first_name' => 'Jane',
            'middle_name' => '',
            'last_name' => 'Tester',
            'job_occupation' => 'QA Lead',
            'unit_name' => 'Support',
            'email' => 'jane.tester@example.com',
            'telephone' => '123456789',
            'mobile' => '123456789',
            'receive_price_list' => 0,
            'receive_invoice' => 0,
            'receive_report' => 0,
            'receive_feedback' => 1,
            'company_id' => 1,
            'crm_customer_id' => $this->customer->id,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'can_login' => 0,
        ]);

        $this->service = app(FeedbackCorrectiveActionService::class);

        $this->actingAs($this->user);
        session([
            'permissions' => [
                'CRM' => [
                    'components' => [
                        'Feedbacks' => [
                            'View' => 'true',
                            'Edit' => 'true',
                            'Add' => 'true',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_feedback_with_explicit_issue_creates_corrective_action_draft_and_issue_item(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => true,
            'issue_description' => 'Delayed report issuance affected our release schedule.',
        ]);

        $correctiveAction = $this->service->ensureFromFeedback($feedback, $this->user->id);

        $this->assertNotNull($correctiveAction);
        $this->assertSame('Draft', $correctiveAction->status);
        $this->assertTrue((bool) $correctiveAction->auto_triggered);
        $this->assertDatabaseHas('crm_feedback_corrective_actions', [
            'customer_feedback_id' => $feedback->id,
            'status' => 'Draft',
        ]);
        $this->assertDatabaseHas('crm_feedback_corrective_action_items', [
            'feedback_corrective_action_id' => $correctiveAction->id,
            'source_type' => 'reported_issue',
            'issue_summary' => 'Delayed report issuance affected our release schedule.',
        ]);
    }

    public function test_feedback_with_low_dynamic_rating_creates_items_per_low_metric(): void
    {
        $feedback = $this->createFeedback();
        $metricA = $this->createMetric('Turnaround Time', 5, 1);
        $metricB = $this->createMetric('Communication', 5, 2);
        $metricC = $this->createMetric('Professionalism', 5, 3);

        FeedbackRating::create([
            'customer_feedback_id' => $feedback->id,
            'evaluation_metric_id' => $metricA->id,
            'rating' => 2,
        ]);
        FeedbackRating::create([
            'customer_feedback_id' => $feedback->id,
            'evaluation_metric_id' => $metricB->id,
            'rating' => 1,
        ]);
        FeedbackRating::create([
            'customer_feedback_id' => $feedback->id,
            'evaluation_metric_id' => $metricC->id,
            'rating' => 4,
        ]);

        $correctiveAction = $this->service->ensureFromFeedback($feedback->fresh('ratings.metric'), $this->user->id);

        $this->assertNotNull($correctiveAction);
        $this->assertCount(2, $correctiveAction->items);
        $this->assertDatabaseHas('crm_feedback_corrective_action_items', [
            'feedback_corrective_action_id' => $correctiveAction->id,
            'source_type' => 'low_rating',
            'evaluation_metric_id' => $metricA->id,
        ]);
        $this->assertDatabaseHas('crm_feedback_corrective_action_items', [
            'feedback_corrective_action_id' => $correctiveAction->id,
            'source_type' => 'low_rating',
            'evaluation_metric_id' => $metricB->id,
        ]);
    }

    public function test_feedback_without_issue_or_low_ratings_does_not_create_corrective_action(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => false,
            'issue_description' => null,
        ]);
        $metric = $this->createMetric('Accuracy', 5, 1);

        FeedbackRating::create([
            'customer_feedback_id' => $feedback->id,
            'evaluation_metric_id' => $metric->id,
            'rating' => 4,
        ]);

        $correctiveAction = $this->service->ensureFromFeedback($feedback->fresh('ratings.metric'), $this->user->id);

        $this->assertNull($correctiveAction);
        $this->assertDatabaseMissing('crm_feedback_corrective_actions', [
            'customer_feedback_id' => $feedback->id,
        ]);
    }

    public function test_repeated_evaluation_reuses_existing_corrective_action(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => true,
            'issue_description' => 'Incorrect sample labeling on the report.',
        ]);

        $first = $this->service->ensureFromFeedback($feedback, $this->user->id);
        $second = $this->service->ensureFromFeedback($feedback->fresh('ratings.metric'), $this->user->id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, DB::table('crm_feedback_corrective_actions')->where('customer_feedback_id', $feedback->id)->count());
    }

    public function test_feedback_list_renders_actions_dropdown_and_corrective_action_entry(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => true,
            'issue_description' => 'Escalation needed for delayed communication.',
        ]);
        $this->service->ensureFromFeedback($feedback, $this->user->id);

        Livewire::test(FeedbackList::class)
            ->assertSee('Send Feedback')
            ->assertSee('View Corrective Action')
            ->assertSee('Corrective Action');
    }

    public function test_customer_feedback_tab_shows_corrective_action_status(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => true,
            'issue_description' => 'Customer flagged unresolved sample discrepancy.',
        ]);

        $correctiveAction = $this->service->ensureFromFeedback($feedback, $this->user->id);

        Livewire::test(CustomerFeedbacksTab::class, ['customer' => $this->customer])
            ->assertSee($correctiveAction->reference_no)
            ->assertSee('View Corrective Action');
    }

    public function test_low_score_alert_email_includes_corrective_action_link_and_metric_values(): void
    {
        $feedback = $this->createFeedback([
            'has_issues' => true,
            'issue_description' => 'Response time was below expectations.',
        ]);
        $metric = $this->createMetric('Turnaround Time', 5, 1);

        FeedbackRating::create([
            'customer_feedback_id' => $feedback->id,
            'evaluation_metric_id' => $metric->id,
            'rating' => 1,
        ]);

        $correctiveAction = $this->service->ensureFromFeedback($feedback->fresh('ratings.metric'), $this->user->id);
        $evaluation = $this->service->evaluate($feedback->fresh('ratings.metric'));

        $html = (new LowScoreAlert(
            $feedback->fresh('contact.customer'),
            $evaluation['low_ratings'],
            $this->service->buildCorrectiveActionUrl($feedback)
        ))->render();

        $this->assertStringContainsString('Turnaround Time', $html);
        $this->assertStringContainsString('1/5', $html);
        $this->assertNotEmpty($correctiveAction->reference_no);
        $this->assertStringContainsString('Open corrective action in CRM', $html);
        $this->assertStringContainsString('openCorrectiveAction=' . $feedback->id, $html);
    }

    public function test_pending_feedback_still_shows_resend_request_action(): void
    {
        $feedback = $this->createFeedback([
            'status' => CustomerFeedback::STATUS_PENDING,
            'is_submitted' => false,
        ]);

        Livewire::test(FeedbackList::class)
            ->assertSee($feedback->code)
            ->assertSee('Resend Request');
    }

    protected function createFeedback(array $overrides = []): CustomerFeedback
    {
        $defaults = [
            'customer_id' => $this->customer->id,
            'contact_id' => $this->contactId,
            'feedback' => 'Customer feedback body for corrective action testing.',
            'received_from' => $this->customer->name,
            'registered_by' => $this->user->name,
            'user_type' => 'Customer',
            'date' => now(),
            'status' => CustomerFeedback::STATUS_SUBMITTED,
            'is_submitted' => true,
            'submitted_at' => now(),
            'service_type' => 'Testing',
            'service_reference_no' => 'SR-' . strtoupper(substr(md5((string) microtime()), 0, 6)),
            'code' => 'FB' . random_int(1000, 9999),
            'has_issues' => false,
            'issue_description' => null,
        ];

        return CustomerFeedback::create(array_merge($defaults, $overrides));
    }

    protected function createMetric(string $name, int $maxRating, int $displayOrder): EvaluationMetric
    {
        return EvaluationMetric::create([
            'name' => $name,
            'prompt_text' => $name,
            'max_rating' => $maxRating,
            'rating_labels' => [
                1 => 'Poor',
                2 => 'Fair',
                3 => 'Good',
                4 => 'Very Good',
                5 => 'Excellent',
            ],
            'is_active' => true,
            'display_order' => $displayOrder,
        ]);
    }
}
