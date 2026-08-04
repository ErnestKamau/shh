<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\RequestViewPagePresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestViewPagePresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_for_reception_shows_receive_samples_action(): void
    {
        [$form, $instance, $enquiry] = $this->createTrfWithEnquiry(
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION
        );

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: $enquiry,
            isTrfForm: true,
        );

        $this->assertSame(
            RequestViewPagePresenter::STAGE_READY_FOR_RECEPTION,
            $presenter->enquiryDisplayStatus()
        );

        $actions = $presenter->nextStepActions('Samples Receiving');
        $keys = $this->actionKeys($actions);

        $this->assertSame('receive_samples', $actions['primary']['key'] ?? null);
        $this->assertSame('Receive Samples', $actions['primary']['label'] ?? null);
        $this->assertNotContains('process_enquiry', $keys);
        $this->assertNotContains('record_po', $keys);
        $this->assertNotContains('record_walk_in_acceptance', $keys);
        $this->assertNotContains('open_receiving_board', $keys);
        $this->assertNotContains('edit', $keys);
        $this->assertNotContains('accept_samples', $keys);
        $this->assertContains('receive_samples', $keys);
    }

    public function test_sample_integrity_check_hides_accept_sample_action(): void
    {
        [$form, $instance, $enquiry] = $this->createTrfWithEnquiry(
            SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK
        );

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: $enquiry,
            isTrfForm: true,
        );

        $this->assertSame(
            RequestViewPagePresenter::STAGE_SAMPLE_INTEGRITY_CHECK,
            $presenter->enquiryDisplayStatus()
        );

        $actions = $presenter->nextStepActions('Samples Receiving');
        $keys = $this->actionKeys($actions);

        $this->assertNull($actions['primary']);
        $this->assertNotContains('receive_samples', $keys);
        $this->assertNotContains('accept_samples', $keys);
    }

    public function test_legacy_in_review_enquiry_maps_to_ready_for_reception_without_receive_primary(): void
    {
        [$form, $instance, $enquiry] = $this->createTrfWithEnquiry(
            SampleSubmissionRequest::STATUS_IN_REVIEW
        );

        $instance->update(['status' => 'in_review']);
        $instance = $instance->fresh(['batches', 'analysisAcceptanceForms']);

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: $enquiry->fresh(),
        );

        $this->assertSame(
            RequestViewPagePresenter::STAGE_READY_FOR_RECEPTION,
            $presenter->enquiryDisplayStatus()
        );

        $actions = $presenter->nextStepActions('Samples Receiving');
        $keys = $this->actionKeys($actions);

        // Legacy In Review display stage still maps to Ready for Reception stage label.
        $this->assertSame('receive_samples', $actions['primary']['key'] ?? null);
        $this->assertSame('Receive Samples', $actions['primary']['label'] ?? null);
        $this->assertNotContains('open_review_board', $keys);
        $this->assertNotContains('process_enquiry', $keys);
        $this->assertNotContains('record_po', $keys);
        $this->assertNotContains('accept_samples', $keys);
    }

    public function test_accepted_enquiry_shows_accepted_and_hides_accept_sample(): void
    {
        [$form, $instance, $enquiry] = $this->createTrfWithEnquiry('received_at_lab');

        $instance->update(['status' => 'approved']);
        $instance = $instance->fresh(['batches', 'analysisAcceptanceForms']);

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: $enquiry->fresh(),
        );

        $this->assertSame(
            RequestViewPagePresenter::STAGE_ACCEPTED,
            $presenter->enquiryDisplayStatus()
        );

        $actions = $presenter->nextStepActions('Samples Receiving');
        $keys = $this->actionKeys($actions);

        $this->assertNull($actions['primary']);
        $this->assertNotContains('accept_samples', $keys);
        $this->assertNotContains('receive_samples', $keys);
        $this->assertNotContains('process_enquiry', $keys);
        $this->assertNotContains('record_po', $keys);
    }

    public function test_empty_and_submit_sign_sections_are_omitted_from_cards(): void
    {
        [$form, $instance] = $this->createBareFormAndInstance();

        $formData = [
            'sections' => [
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Customer details',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'customer_name',
                                    'label' => 'Name',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => 'Acme Labs', 'display_value' => 'Acme Labs'],
                                    ],
                                ],
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'customer_email',
                                    'label' => 'Email',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => '', 'display_value' => 'N/A'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Sample collection data',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'sampling_date',
                                    'label' => 'Sampling date',
                                    'element_type' => 'date',
                                    'saved_values' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Submit & sign',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'remarks',
                                    'label' => 'Remarks',
                                    'element_type' => 'textarea',
                                    'saved_values' => [
                                        ['value' => 'Signed', 'display_value' => 'Signed'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Test & sample information',
                    'section_type' => 'rows_section',
                    'element_holders' => [
                        [
                            'holder_type' => 'rows',
                            'elements' => [],
                            'rows_data' => [],
                        ],
                    ],
                ],
            ],
        ];

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: null,
        );

        $cards = $presenter->sectionCards($formData);

        $this->assertNotNull($cards['customer']);
        $this->assertCount(1, $cards['customer']['fields']);
        $this->assertSame('customer_name', $cards['customer']['fields'][0]['name']);
        $this->assertSame([], $cards['sections']);
    }

    public function test_header_uses_enquiry_stage_without_form_number_or_progress(): void
    {
        [$form, $instance, $enquiry] = $this->createTrfWithEnquiry(
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED
        );

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: $enquiry,
        );

        $header = $presenter->header();

        $this->assertSame(RequestViewPagePresenter::STAGE_QUOTATION_ACCEPTED, $header['enquiry_stage']);
        $this->assertNotEmpty($header['request_number']);
        $this->assertSame($form->name, $header['form_name']);
        $this->assertArrayNotHasKey('form_number', $header);
        $this->assertArrayNotHasKey('progress_percent', $header);
    }

    public function test_request_info_card_orders_fixed_fields_and_hides_empty_dynamic_fields(): void
    {
        [$form, $instance] = $this->createBareFormAndInstance();

        $formData = [
            'sections' => [
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Customer details',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'customer_name',
                                    'label' => 'Client name',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => 'Acme Labs', 'display_value' => 'Acme Labs'],
                                    ],
                                ],
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'customer_email',
                                    'label' => 'Email',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => 'ops@acme.test', 'display_value' => 'ops@acme.test'],
                                    ],
                                ],
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'mobile_number',
                                    'label' => 'Mobile',
                                    'element_type' => 'text',
                                    'saved_values' => [
                                        ['value' => '', 'display_value' => 'N/A'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Sample collection data',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'sampling_date',
                                    'label' => 'Sampling date',
                                    'element_type' => 'date',
                                    'saved_values' => [
                                        ['value' => '2026-07-01', 'display_value' => '2026-07-01'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Submit & sign',
                    'section_type' => 'regular',
                    'element_holders' => [
                        [
                            'holder_type' => 'field',
                            'elements' => [
                                [
                                    'id' => (string) Str::uuid7(),
                                    'name' => 'remarks',
                                    'label' => 'Remarks',
                                    'element_type' => 'textarea',
                                    'saved_values' => [
                                        ['value' => 'Handle with care', 'display_value' => 'Handle with care'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => (string) Str::uuid7(),
                    'title' => 'Test & sample information',
                    'section_type' => 'rows_section',
                    'element_holders' => [
                        [
                            'holder_type' => 'rows',
                            'elements' => [],
                            'rows_data' => [],
                        ],
                    ],
                ],
            ],
        ];

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: null,
        );

        $card = $presenter->requestInfoCard($formData, []);

        $labels = array_column($card['fields'], 'label');
        $this->assertSame('Client name', $labels[0]);
        $this->assertContains('Email', $labels);
        $this->assertNotContains('Mobile number', $labels);
        $this->assertContains('Sampling date', $labels);
        $this->assertSame('Handle with care', $card['remarks']);
    }

    public function test_test_samples_card_exposes_parameter_groups_and_description(): void
    {
        [$form, $instance] = $this->createBareFormAndInstance();

        $presenter = new RequestViewPagePresenter(
            instance: $instance,
            submissionForm: $form,
            commercialEnquiry: null,
        );

        $card = $presenter->testSamplesCard([
            [
                'row_index' => 0,
                'customer_sample_id' => 'S-1',
                'sample_type_name' => 'Water',
                'analysis_type_name' => 'Microbiology',
                'sample_quantity' => '500',
                'sample_quantity_unit' => 'ml',
                'sample_description' => '<p>Clear bottled water</p>',
                'sampling_point' => 'Plant A',
                'production_date' => '2026-01-01',
                'expiration_date' => '2026-12-31',
                'batch_number' => 'BN-9',
                'parameter_label' => 'TPC, Yeast',
                'attributes' => [
                    'test_category' => 'Routine',
                    'analysis_element_ids' => [],
                ],
            ],
        ]);

        $this->assertSame(1, $card['count']);
        $sample = $card['samples'][0];
        $this->assertSame('Water', $sample['sample_type']);
        $this->assertSame('Microbiology', $sample['analysis_type']);
        $this->assertSame('500 ml', $sample['sample_quantity']);
        $this->assertTrue($sample['has_description']);
        $this->assertStringContainsString('Clear bottled water', $sample['sample_description_html']);
        $this->assertSame('Plant A', $sample['sampling_point']);
        $this->assertSame('Routine', $sample['test_category']);
        $this->assertNotEmpty($sample['parameter_groups']);
    }

    /**
     * @param  array{primary: ?array<string, mixed>, secondary: list<array<string, mixed>>, danger: ?array<string, mixed>}  $actions
     * @return list<string>
     */
    private function actionKeys(array $actions): array
    {
        $keys = [];
        if (($actions['primary']['key'] ?? null) !== null) {
            $keys[] = $actions['primary']['key'];
        }
        foreach ($actions['secondary'] as $action) {
            $keys[] = $action['key'];
        }

        return $keys;
    }

    /**
     * @return array{0: SubmissionForm, 1: SubmissionFormInstance, 2: SampleSubmissionRequest}
     */
    private function createTrfWithEnquiry(string $status): array
    {
        [$form, $instance] = $this->createBareFormAndInstance();

        $enquiry = SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid7(),
            'submission_form_instance_id' => $instance->id,
            'status' => $status,
            'source_channel' => 'walk_in',
            'request_number' => 42,
        ]);

        return [$form, $instance, $enquiry];
    }

    /**
     * @return array{0: SubmissionForm, 1: SubmissionFormInstance}
     */
    private function createBareFormAndInstance(): array
    {
        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'AmSpec Food Test Request Form',
            'document_code' => 'TRF-FOOD-019',
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
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
            'title' => 'Portal request',
            'form_number' => 'CR200',
            'status' => 'submitted',
            'submitted_at' => now(),
            'priority' => 'normal',
        ]);

        return [$form, $instance];
    }
}
