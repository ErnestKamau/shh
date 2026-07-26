<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\System\SystemConfigurationsType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_configurations', function (Blueprint $table) {
            if (! Schema::hasColumn('system_configurations', 'meta')) {
                $table->json('meta')->nullable()->after('value');
            }
        });

        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'payment_terms_note')) {
                $table->string('payment_terms_note', 500)->nullable()->after('credit_days');
            }
        });

        $type = SystemConfigurationsType::firstOrCreate(
            ['configuration_type' => 'Account Settings'],
            [
                'description' => 'Customer account billing / credit status options.',
                'status' => true,
            ]
        );

        $options = [
            [
                'key' => 'Account Holder(OK)',
                'value' => 'Account Holder(OK)',
                'meta' => [
                    'billing_type' => 'credit',
                    'days' => null,
                    'anchor' => 'invoice_created',
                    'payment_method' => null,
                    'allows_custom_days' => true,
                    'label' => 'Account Holder(OK)',
                ],
            ],
            [
                'key' => 'Account Holder(Overdue)',
                'value' => 'Account Holder(Overdue)',
                'meta' => [
                    'billing_type' => 'walk_in',
                    'days' => null,
                    'anchor' => 'invoice_created',
                    'payment_method' => null,
                    'allows_custom_days' => true,
                    'label' => 'Account Holder(Overdue)',
                ],
            ],
            [
                'key' => 'Pay Upfront',
                'value' => 'Pay Upfront',
                'meta' => [
                    'billing_type' => 'advance',
                    'days' => 0,
                    'anchor' => 'immediate',
                    'payment_method' => null,
                    'allows_custom_days' => false,
                    'label' => 'Pay Upfront',
                ],
            ],
            [
                'key' => '20 days after Test Report, bank slip',
                'value' => '20 days after delivery of the Test Report, payment by bank slip',
                'meta' => [
                    'billing_type' => 'credit',
                    'days' => 20,
                    'anchor' => 'test_report_delivery',
                    'payment_method' => 'bank_slip',
                    'allows_custom_days' => false,
                    'label' => '20 days after delivery of the Test Report, payment by bank slip',
                ],
            ],
            [
                'key' => '30 days after Test Report, bank slip',
                'value' => '30 days after delivery of the Test Report, payment by bank slip',
                'meta' => [
                    'billing_type' => 'credit',
                    'days' => 30,
                    'anchor' => 'test_report_delivery',
                    'payment_method' => 'bank_slip',
                    'allows_custom_days' => false,
                    'label' => '30 days after delivery of the Test Report, payment by bank slip',
                ],
            ],
            [
                'key' => '45 days after Test Report, bank slip',
                'value' => '45 days after delivery of the Test Report, payment by bank slip',
                'meta' => [
                    'billing_type' => 'credit',
                    'days' => 45,
                    'anchor' => 'test_report_delivery',
                    'payment_method' => 'bank_slip',
                    'allows_custom_days' => false,
                    'label' => '45 days after delivery of the Test Report, payment by bank slip',
                ],
            ],
            [
                'key' => 'Payment in advance by bank slip',
                'value' => 'Payment in advance by bank slip / bank deposit',
                'meta' => [
                    'billing_type' => 'advance',
                    'days' => 0,
                    'anchor' => 'immediate',
                    'payment_method' => 'bank_slip',
                    'allows_custom_days' => false,
                    'label' => 'Payment in advance by bank slip / bank deposit',
                ],
            ],
            [
                'key' => 'Other',
                'value' => 'Other – editable',
                'meta' => [
                    'billing_type' => 'other',
                    'days' => null,
                    'anchor' => 'invoice_created',
                    'payment_method' => null,
                    'allows_custom_days' => true,
                    'label' => 'Other – editable',
                ],
            ],
        ];

        foreach ($options as $option) {
            $existing = $type->configurations()->where('key', $option['key'])->first();

            if ($existing) {
                $existing->value = $option['value'];
                $existing->meta = $option['meta'];
                $existing->status = true;
                $existing->save();
            } else {
                $type->configurations()->create([
                    'key' => $option['key'],
                    'value' => $option['value'],
                    'meta' => $option['meta'],
                    'status' => true,
                ]);
            }
        }

        app(\App\Services\Commercial\AccountPaymentTermsService::class)->clearAccountSettingsCache();
    }

    public function down(): void
    {
        app(\App\Services\Commercial\AccountPaymentTermsService::class)->clearAccountSettingsCache();

        $type = SystemConfigurationsType::where('configuration_type', 'Account Settings')->first();

        if ($type) {
            $type->configurations()
                ->whereIn('key', [
                    '20 days after Test Report, bank slip',
                    '30 days after Test Report, bank slip',
                    '45 days after Test Report, bank slip',
                    'Payment in advance by bank slip',
                    'Other',
                ])
                ->delete();
        }

        Schema::table('crm_customers', function (Blueprint $table) {
            if (Schema::hasColumn('crm_customers', 'payment_terms_note')) {
                $table->dropColumn('payment_terms_note');
            }
        });

        Schema::table('system_configurations', function (Blueprint $table) {
            if (Schema::hasColumn('system_configurations', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
