<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\CRM\CustomerNotification;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private const GATEWAY_KEY = 'test-portal-gateway-key';

    private const CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7e';

    private const OTHER_CUSTOMER_ID = '019e2d90-ce2d-70af-882c-0b22576a6b7f';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.portal_gateway.api_key' => self::GATEWAY_KEY,
            'dashboard.cache.store' => 'array',
        ]);
    }

    public function test_main_dashboard_returns_aggregated_payload(): void
    {
        $form = $this->createPortalForm();
        $this->createInstance($form, ['status' => 'submitted', 'submitted_at' => now()]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer_id', self::CUSTOMER_ID)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'customer_id',
                    'summary' => [
                        'submissions',
                        'invoices',
                        'feedback',
                        'counts',
                    ],
                    'recent_submissions',
                    'recent_reports',
                    'notifications',
                    'recent_complaints',
                    'recent_feedback',
                ],
            ]);
    }

    public function test_rejects_customer_id_mismatch(): void
    {
        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::OTHER_CUSTOMER_ID)
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_requires_gateway_authentication(): void
    {
        $this->withHeaders([
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ])->getJson('/api/v1/dashboard/'.self::CUSTOMER_ID)
            ->assertUnauthorized();
    }

    public function test_analytics_endpoint_returns_chart_datasets(): void
    {
        $form = $this->createPortalForm();
        $this->createInstance($form, ['status' => 'in_review', 'submitted_at' => now()]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID.'/analytics')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'monthly_submission_trends',
                    'submission_status_distribution',
                    'invoice_trends',
                    'request_type_analytics',
                    'workflow_turnaround',
                    'complaint_insights',
                    'feedback_analytics',
                ],
            ]);
    }

    public function test_notifications_are_paginated(): void
    {
        CustomerNotification::query()->create([
            'id' => (string) Str::uuid7(),
            'customer_id' => self::CUSTOMER_ID,
            'entity_type' => SubmissionFormInstance::class,
            'entity_id' => (string) Str::uuid7(),
            'notification_type' => 'Test alert',
            'notification_description' => 'Please review your submission.',
        ]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID.'/notifications')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.notifications.0.title', 'Test alert');
    }

    public function test_reports_are_visible_when_legacy_storage_path_is_present(): void
    {
        SampleHeader::query()->create([
            'batch_code' => 'BA-PORTAL-0001',
            'receipt_date' => now()->toDateString(),
            'date_collected' => now()->toDateString(),
            'date_expected' => now()->addDay()->toDateString(),
            'crm_customer_id' => self::CUSTOMER_ID,
            'crm_unit_name' => 'Unit A',
            'sample_type_id' => 1,
            'status' => config('dashboard.report_status', 'Completed'),
            'batch_report_url' => '/reports/acme/BA-PORTAL-0001.pdf',
        ]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID.'/reports')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.report_number', 'BA-PORTAL-0001')
            ->assertJsonPath('data.reports.0.download_url', url('/storage/reports/acme/BA-PORTAL-0001.pdf'));
    }

    public function test_reports_are_visible_after_portal_delivery_without_completing_workflow_status(): void
    {
        $batch = SampleHeader::query()->create([
            'batch_code' => 'BA-PORTAL-0002',
            'receipt_date' => now()->toDateString(),
            'date_collected' => now()->toDateString(),
            'date_expected' => now()->addDay()->toDateString(),
            'crm_customer_id' => self::CUSTOMER_ID,
            'crm_unit_name' => 'Unit A',
            'sample_type_id' => 1,
            'status' => 'Sample Approval',
            'batch_report_url' => '/reports/acme/BA-PORTAL-0002.pdf',
            'batch_report_online_url' => url('/storage/reports/acme/BA-PORTAL-0002.pdf'),
        ]);

        \App\Models\TestRequestReportDelivery::query()->create([
            'batch_id' => $batch->id,
            'revision_no' => 1,
            'channel' => 'portal',
            'recipient_name' => 'Customer Portal',
            'status' => 'sent',
        ]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID.'/reports')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.reports.0.report_number', 'BA-PORTAL-0002');
    }

    public function test_generated_reports_are_hidden_until_portal_delivery_or_completed_status(): void
    {
        SampleHeader::query()->create([
            'batch_code' => 'BA-PORTAL-0003',
            'receipt_date' => now()->toDateString(),
            'date_collected' => now()->toDateString(),
            'date_expected' => now()->addDay()->toDateString(),
            'crm_customer_id' => self::CUSTOMER_ID,
            'crm_unit_name' => 'Unit A',
            'sample_type_id' => 1,
            'status' => 'Sample Approval',
            'batch_report_url' => '/reports/acme/BA-PORTAL-0003.pdf',
        ]);

        $this->dashboardRequest('GET', '/api/v1/dashboard/'.self::CUSTOMER_ID.'/reports')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0);
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function dashboardRequest(string $method, string $uri, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge([
            'Authorization' => 'Bearer '.self::GATEWAY_KEY,
            'Accept' => 'application/json',
            'X-CRM-Customer-Id' => self::CUSTOMER_ID,
        ], $headers))->json($method, $uri);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPortalForm(array $overrides = []): SubmissionForm
    {
        return SubmissionForm::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'name' => 'Dashboard Form',
            'document_code' => 'DASH/01',
            'description' => 'Test',
            'naming_convention_prefix' => 'DR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'is_customer_request_form' => false,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'water',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => ['sample-workflow'],
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createInstance(SubmissionForm $form, array $overrides = []): SubmissionFormInstance
    {
        return SubmissionFormInstance::query()->create(array_merge([
            'id' => (string) Str::uuid7(),
            'submission_form_id' => $form->id,
            'title' => 'Dashboard instance',
            'status' => 'draft',
            'crm_customer_id' => self::CUSTOMER_ID,
            'priority' => 'normal',
        ], $overrides));
    }
}
