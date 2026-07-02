<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\EnquiryReviewDisplayService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnquiryReviewDisplayServiceTest extends TestCase
{
    #[Test]
    public function sample_rows_render_entity_encoded_rich_text_as_html(): void
    {
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [
                [
                    'sample_description' => '&lt;p&gt;&lt;strong&gt;Chicken&lt;/strong&gt;&lt;/p&gt;',
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
                ],
            ],
        ]);

        $rows = app(EnquiryReviewDisplayService::class)->sampleRows($enquiry);

        $this->assertCount(1, $rows);
        $this->assertSame('<p><em>Tap water</em></p>', $rows[0]['sample_description_html']);
        $this->assertSame('Tap water', $rows[0]['sample_description']);
    }
}
