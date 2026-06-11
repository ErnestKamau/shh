<?php

namespace Tests\Unit\Services\Billing;

use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationReportServiceTest extends TestCase
{
    public function test_build_placeholder_groups_matches_amspec_demo_structure(): void
    {
        $service = app(QuotationReportService::class);

        $groups = $service->buildPlaceholderGroups();

        $this->assertCount(4, $groups);
        $this->assertSame('Non Seafood', $groups[0]['sample_type_name']);
        $this->assertSame('Seafood', $groups[1]['sample_type_name']);
        $this->assertSame('Tap Water', $groups[2]['sample_type_name']);
        $this->assertSame('Swimming pool', $groups[3]['sample_type_name']);

        $this->assertCount(4, $groups[0]['rows']);
        $this->assertCount(4, $groups[1]['rows']);
        $this->assertCount(4, $groups[2]['rows']);
        $this->assertCount(3, $groups[3]['rows']);

        $this->assertSame('Test#1', $groups[0]['rows'][0]['test_name']);
        $this->assertTrue($groups[0]['rows'][0]['is_placeholder']);
        $this->assertSame(100.0, $groups[0]['rows'][0]['unit_price']);
        $this->assertSame('', $groups[0]['rows'][0]['test_method']);
        $this->assertSame('', $groups[0]['rows'][0]['loq']);
        $this->assertSame('', $groups[0]['rows'][0]['mu_percent']);
    }

    public function test_placeholder_groups_provide_rowspan_ready_row_counts(): void
    {
        $service = app(QuotationReportService::class);
        $groups = $service->buildPlaceholderGroups();

        foreach ($groups as $group) {
            $this->assertNotEmpty($group['sample_type_name']);
            $this->assertGreaterThanOrEqual(3, count($group['rows']));

            foreach ($group['rows'] as $row) {
                $this->assertArrayHasKey('test_name', $row);
                $this->assertArrayHasKey('unit_price', $row);
                $this->assertTrue($row['is_placeholder']);
            }
        }
    }

    public function test_public_report_token_is_stable_for_quotation(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->id = (string) Str::uuid();

        $first = $service->publicReportToken($header);
        $second = $service->publicReportToken($header);

        $this->assertSame(40, strlen($first));
        $this->assertSame($first, $second);
    }

    public function test_resolve_report_view_url_includes_token_route(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->id = (string) Str::uuid();

        $url = $service->resolveReportViewUrl($header);

        $this->assertStringContainsString('/billing/quotations/'.$header->id.'/report/', $url);
        $this->assertStringContainsString($service->publicReportToken($header), $url);
    }
}
