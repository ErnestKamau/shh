<?php

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'Terms of Sale'],
            [
                'description' => 'Default Terms of Sale text for quotations in preparation.',
                'status' => true,
            ]
        );

        $defaults = [
            'service_delivery' => 'Results are available by e-mail/portal to the contact provided by the applicant in the registration form.',
            'quote_specification' => 'Amspec Lab recommends that the customer send the proposal with signature in the acceptance field. Placement of the purchase order or shipment of samples to our laboratory also means acceptance',
            'additional_info' => 'qualidade.labcwb@amspecgroup.com',
            'payment_info' => implode("\n", [
                'Legal Name: AMSPEC BRASIL PARTICIPACOES LTDA',
                'Trade Name: AMSPEC BRAZIL AGRI & FOOD LAB',
                'CNPJ: 28.699.788/0016-71',
                'Address: Marechal Floriano Peixoto, 7556, Boqueirão Curitiba PR – Brazil ZIP Code 81.670-000',
                'Contact 41 99189-6141 / AgriLab.Curitiba@amspecgroup.com.',
            ]),
        ];

        foreach ($defaults as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        SystemConfiguration::query()
            ->whereIn('key', [
                'service_delivery',
                'quote_specification',
                'additional_info',
                'payment_info',
            ])
            ->delete();

        $type = SystemConfigurationsType::query()
            ->where('configuration_type', 'Terms of Sale')
            ->first();

        if ($type !== null && $type->configurations()->count() === 0) {
            $type->delete();
        }
    }
};
