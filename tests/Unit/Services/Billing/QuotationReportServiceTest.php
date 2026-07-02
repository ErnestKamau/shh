<?php

namespace Tests\Unit\Services\Billing;

use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationReportServiceTest extends TestCase
{
    public function test_build_view_data_marks_empty_quotes_without_placeholder_rows(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->id = (string) Str::uuid();

        $data = $service->buildViewData($header);

        $this->assertFalse($data['hasLineItems']);
        $this->assertSame([], $data['groups']);
        $this->assertFalse($data['isPlaceholderTable']);
    }

    public function test_plaintext_value_decrypts_laravel_ciphertext(): void
    {
        $service = app(QuotationReportService::class);
        $plain = 'Report within 7 working days.';
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($plain);

        $this->assertSame($plain, $service->plaintextValue($encrypted));
        $this->assertSame($plain, $service->plaintextValue($plain));
        $this->assertSame('', $service->plaintextValue(null));
    }

    public function test_resolve_terms_of_sale_decrypts_header_fields(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->service_delivery = \Illuminate\Support\Facades\Crypt::encryptString('7 days after sample submission');

        $terms = $service->resolveTermsOfSale($header);

        $this->assertSame('7 days after sample submission', $terms['service_delivery']);
        $this->assertNotEmpty($terms['payment_info']);
    }

    public function test_resolve_terms_of_sale_falls_back_when_header_value_stays_encrypted(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->service_delivery = 'eyJpdiI6InVucmVhZGFibGUiLCJ2YWx1ZSI6ImJsb2IiLCJtYWMiOiJ0ZXN0In0=';

        $terms = $service->resolveTermsOfSale($header);

        $this->assertNotSame($header->service_delivery, $terms['service_delivery']);
        $this->assertFalse(str_starts_with($terms['service_delivery'], 'eyJ'));
    }

    public function test_resolve_terms_of_sale_falls_back_to_config_defaults(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->service_delivery = '7 days after sample submission';

        $terms = $service->resolveTermsOfSale($header);

        $this->assertSame('7 days after sample submission', $terms['service_delivery']);
        $this->assertNotEmpty($terms['payment_info']);
    }

    public function test_plaintext_value_decrypts_nested_ciphertext(): void
    {
        $service = app(QuotationReportService::class);
        $plain = 'Nested terms value';
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString(
            \Illuminate\Support\Facades\Crypt::encryptString($plain)
        );

        $this->assertSame($plain, $service->plaintextValue($encrypted));
    }

    public function test_resolve_terms_falls_back_to_defaults_when_config_missing(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();

        $terms = $service->resolveTerms($header);

        $this->assertNotEmpty($terms['items']);
        $this->assertSame(1, $terms['items'][0]['number']);
        $this->assertStringContainsString('30 days', $terms['items'][0]['text']);
    }

    public function test_resolve_terms_uses_override_lines_when_provided(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->terms_override = "Custom term one\nCustom term two";

        $terms = $service->resolveTerms($header);

        $this->assertCount(2, $terms['items']);
        $this->assertSame('Custom term one', $terms['items'][0]['text']);
    }

    public function test_resolve_structured_terms_merges_header_values_with_defaults(): void
    {
        $service = app(QuotationReportService::class);
        $header = new QuotationHeader();
        $header->structured_terms = [
            'tat' => '5 working days',
            'vat' => '5% VAT applies',
        ];

        $terms = $service->resolveStructuredTerms($header);

        $this->assertNotEmpty($terms['items']);
        $this->assertSame('5 working days', $terms['items'][0]['value']);
        $this->assertSame('Turnaround Time (TAT)', $terms['items'][0]['label']);
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
