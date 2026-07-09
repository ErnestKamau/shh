<?php

namespace Database\Seeders\Setup;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\Services\System\ThemeService;
use Illuminate\Database\Seeder;

class QuotationReportConfigSeeder extends Seeder
{
    /** @var list<string> */
    private const BRANDING_ONLY_KEYS = [
        'sys_quotation_primary_color',
        'sys_quotation_accent_color',
    ];

    public function run(): void
    {
        $quotationType = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'Quotation Report'],
            [
                'description' => 'AmSpec quotation preview and PDF settings.',
                'status' => true,
            ]
        );

        $reportSettings = [
            'quotation_lab_ref_prefix' => 'AMSQ',
            'quotation_intro_text' => 'Thank you for your enquiry, we are pleased to quote you our best prices for the following testing services. Hopefully you will find it competitive and as per your requirements.',
            'quotation_closing_text' => 'We hope our offer will meet with your requirements and look forward to work with you for long time. Should you require further information or assistance, please do not hesitate to contact us.',
            'quotation_legal_entity' => 'AMSPEC MIDDLE EAST INSPECTION & TESTING SERVICES L.L.C',
            'quotation_terms_url' => 'https://www.amspecgroup.com/terms-conditions',
            'sys_quotation_primary_color' => ThemeService::QUOTATION_PRIMARY,
            'sys_quotation_accent_color' => ThemeService::QUOTATION_ACCENT,
        ];

        foreach ($reportSettings as $key => $value) {
            $attributes = [
                'configuration_type_id' => $quotationType->id,
                'value' => $value,
                'status' => true,
            ];

            if (in_array($key, self::BRANDING_ONLY_KEYS, true)) {
                SystemConfiguration::query()->firstOrCreate(
                    ['key' => $key],
                    $attributes
                );

                continue;
            }

            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                $attributes
            );
        }

        $termsType = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'Quotation Terms and Conditions'],
            [
                'description' => 'Numbered terms and conditions for customer quotations.',
                'status' => true,
            ]
        );

        $terms = [
            'term_1' => 'This quotation is valid for 30 days from the date of issue.',
            'term_2' => 'Samples must be submitted/collected in suitable condition and quantity for the requested analysis.',
            'term_3' => 'Report turnaround time commences upon receipt and acceptance of the sample.',
            'term_4' => 'Test results apply only to the samples submitted and tested.',
            'term_5' => 'All client information and test results will be treated as confidential.',
            'term_6' => 'Reports shall not be reproduced except in full without written approval from the laboratory.',
            'term_7' => 'The laboratory reserves the right to subcontract specific tests to competent laboratories when required.',
            'term_8' => 'Complaints and appeals will be handled in accordance with the laboratory\'s documented procedures.',
            'term_9' => 'Samples will be retained and disposed of as per the laboratory\'s retention policy.',
            'term_10' => 'Orders cancelled after confirmation may be subject to applicable charges for work already performed, materials procured, or commitments made by the laboratory.',
            'term_11' => 'Payment shall be made within 30 days credit terms stated in the quotation.',
            'term_12' => 'This quotation is valid for a minimum order value of AED _____________.',
            'term_13' => 'The laboratory shall not be liable for delays caused by circumstances beyond its reasonable control.',
            'term_14' => 'Acceptance of this quotation constitutes acceptance of terms and condition on webpage: NONDISCLOSURE AGREEMENT',
        ];

        foreach ($terms as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $termsType->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }

        $structuredType = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'Quotation Structured Terms'],
            [
                'description' => 'Structured commercial terms for customer quotations.',
                'status' => true,
            ]
        );

        foreach (\App\Services\Billing\QuotationReportService::DEFAULT_STRUCTURED_TERMS as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $structuredType->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }
    }
}
