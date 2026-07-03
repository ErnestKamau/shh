<?php

namespace Tests\Unit\Services\Commercial;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\SampleType;
use App\Services\Commercial\EnquiryReviewDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnquiryReviewDisplayServiceTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function sample_rows_render_entity_encoded_rich_text_as_html(): void
    {
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sample_description' => '&lt;p&gt;&lt;strong&gt;Chicken&lt;/strong&gt;&lt;/p&gt;',
                    'sort_order' => 0,
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertCount(1, $rows);
        $this->assertSame('<p><strong>Chicken</strong></p>', $rows[0]['sample_description_html']);
        $this->assertSame('Chicken', $rows[0]['sample_description']);
    }

    #[Test]
    public function sample_rows_render_raw_rich_text_as_html(): void
    {
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sample_description' => '<p><em>Tap water</em></p>',
                    'sort_order' => 0,
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertCount(1, $rows);
        $this->assertSame('<p><em>Tap water</em></p>', $rows[0]['sample_description_html']);
        $this->assertSame('Tap water', $rows[0]['sample_description']);
    }

    #[Test]
    public function sample_rows_group_trf_row_with_sample_type_analysis_types_and_test_codes(): void
    {
        $sampleTypeId = (string) Str::uuid7();
        $analysisTypeId = (string) Str::uuid7();
        $elementA = (string) Str::uuid7();
        $elementB = (string) Str::uuid7();
        $analyteA = (string) Str::uuid7();
        $analyteB = (string) Str::uuid7();

        SampleType::query()->create([
            'id' => $sampleTypeId,
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        AnalysisType::query()->create([
            'id' => $analysisTypeId,
            'name' => 'Cooked',
            'code' => 'CKD',
            'sample_type_id' => $sampleTypeId,
            'active' => 1,
        ]);

        Analyte::query()->create([
            'id' => $analyteA,
            'code' => 'MAP',
            'name' => 'Energy',
            'active' => 1,
        ]);

        Analyte::query()->create([
            'id' => $analyteB,
            'code' => 'ECOLI',
            'name' => 'Moisture and volatile matter',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementA,
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyteA,
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementB,
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyteB,
            'active' => 1,
        ]);

        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sort_order' => 0,
                    'sample_description' => 'Chicken portion',
                    'sample_type_id' => $sampleTypeId,
                    'sample_type_name' => 'Food — Cooked',
                    'analysis_type_id' => $analysisTypeId,
                    'analysis_type_name' => 'Cooked',
                    'attributes' => [
                        'food_sample_type' => 'Cooked',
                        'analysis_element_ids' => [$elementA, $elementB],
                    ],
                    'sample_quantity' => '3',
                    'sample_quantity_unit' => 'kg',
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertCount(1, $rows);
        $this->assertSame('Cooked', $rows[0]['analysis_types']);
        $this->assertSame('MAP, ECOLI', $rows[0]['tests_requested']);
        $this->assertSame(2, $rows[0]['tests_requested_count']);
        $this->assertSame('3 kg', $rows[0]['qty']);
    }

    #[Test]
    public function tests_requested_resolves_parameter_names_to_codes(): void
    {
        $analysisTypeId = (string) Str::uuid7();
        $elementId = (string) Str::uuid7();
        $analyteId = (string) Str::uuid7();

        Analyte::query()->create([
            'id' => $analyteId,
            'code' => 'MAP',
            'name' => 'Energy',
            'active' => 1,
        ]);

        AnalysisType::query()->create([
            'id' => $analysisTypeId,
            'name' => 'Cooked',
            'code' => 'CKD',
            'active' => 1,
        ]);

        AnalysisElements::query()->create([
            'id' => $elementId,
            'analysis_type_id' => $analysisTypeId,
            'analyte_id' => $analyteId,
            'active' => 1,
        ]);

        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sort_order' => 0,
                    'parameter_label' => 'Energy',
                    'analysis_type_id' => $analysisTypeId,
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertSame('MAP', $rows[0]['tests_requested']);
    }

    #[Test]
    public function tests_requested_ignores_test_category_when_no_parameters_selected(): void
    {
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sort_order' => 0,
                    'parameter_label' => 'Microbiology',
                    'parameter_category' => 'microbiology',
                    'sample_quantity' => '1',
                    'sample_quantity_unit' => 'L',
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertSame('—', $rows[0]['tests_requested']);
    }
}
