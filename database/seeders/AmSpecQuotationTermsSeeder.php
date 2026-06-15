<?php

namespace Database\Seeders;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Seeder;

class AmSpecQuotationTermsSeeder extends Seeder
{
    public function run(): void
    {
        $type = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'AmSpec Quotation Terms'],
            [
                'description' => 'Terms and conditions text for AmSpec quotation PDFs.',
                'status' => true,
            ]
        );

        $terms = [
            'term_01' => 'This quotation is valid for thirty (30) days from the date of issue unless otherwise stated.',
            'term_02' => 'Prices are quoted in the currency indicated and are exclusive of VAT unless stated otherwise.',
            'term_03' => 'Turnaround time (TAT) commences upon receipt of samples in acceptable condition and clearance of any required advance payment.',
            'term_04' => 'Samples must be submitted in appropriate containers with complete chain-of-custody documentation where applicable.',
            'term_05' => 'The laboratory reserves the right to subcontract tests that cannot be performed in-house; subcontracted results are provided under the same accreditation scope where applicable.',
            'term_06' => 'Reporting will be issued in the language specified on the test request form unless otherwise agreed in writing.',
            'term_07' => 'Statement of conformity, where requested, will be reported in accordance with the agreed rules of decision.',
            'term_08' => 'Samples not collected within the agreed retention period may be disposed of without further notice.',
            'term_09' => 'Any dispute regarding this quotation must be raised in writing within seven (7) days of receipt.',
            'term_10' => 'Payment terms are as per the customer account agreement or pro-forma invoice requirements.',
            'term_11' => 'The laboratory is not liable for delays caused by factors outside its reasonable control.',
            'term_12' => 'This quotation is valid for a minimum order value of AED __________.',
            'term_13' => 'By accepting this quotation the client agrees to the laboratory terms and conditions of service.',
            'term_14' => 'Acceptance of this quotation constitutes acceptance of terms and conditions on webpage: https://www.amspecgroup.com/terms-conditions',
            'min_order_aed' => '',
            'terms_url' => 'https://www.amspecgroup.com/terms-conditions',
            'footer_text' => 'This quotation is issued subject to the Terms & Conditions on the following page and on https://www.amspecgroup.com/terms-conditions',
            'closing_text' => 'We hope our offer will meet with your requirements and look forward to work with you for long time. Should you require further information or assistance, please do not hesitate to contact us.',
            'signature_company_name' => 'AMSPEC MIDDLE EAST INSPECTION & TESTING SERVICES L.L.C',
        ];

        foreach ($terms as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                [
                    'key' => $key,
                    'configuration_type_id' => $type->id,
                ],
                [
                    'value' => $value,
                    'status' => true,
                ]
            );
        }
    }
}
