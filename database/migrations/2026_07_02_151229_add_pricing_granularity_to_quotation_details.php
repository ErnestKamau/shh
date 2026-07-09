<?php

use App\QuotationDetails;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PRICING_GRANULARITY_ELEMENT = 'element';
    private const PRICING_GRANULARITY_ANALYSIS_TYPE = 'analysis_type';

    public function up(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        if (! Schema::hasColumn('quotation_details', 'pricing_granularity')) {
            Schema::table('quotation_details', function (Blueprint $table) {
                $table->string('pricing_granularity', 32)->nullable()->after('unit_price');
            });
        }

        QuotationDetails::query()
            ->whereNull('pricing_granularity')
            ->orderBy('id')
            ->chunkById(200, function ($details): void {
                foreach ($details as $detail) {
                    $detail->pricing_granularity = $this->inferGranularity($detail);
                    $detail->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        if (Schema::hasColumn('quotation_details', 'pricing_granularity')) {
            Schema::table('quotation_details', function (Blueprint $table) {
                $table->dropColumn('pricing_granularity');
            });
        }
    }

    private function inferGranularity(QuotationDetails $detail): string
    {
        $elementIds = [];

        foreach (['default_analytes', 'accredited_analytes', 'subcontracted_analytes', 'sub_acc_analytes'] as $field) {
            $raw = $detail->{$field};
            if (! $raw) {
                continue;
            }

            foreach (explode(',', (string) $raw) as $id) {
                $id = trim($id);
                if ($id !== '') {
                    $elementIds[] = $id;
                }
            }
        }

        $elementIds = array_values(array_unique($elementIds));

        if (count($elementIds) === 1) {
            return self::PRICING_GRANULARITY_ELEMENT;
        }

        if (count($elementIds) > 1) {
            return self::PRICING_GRANULARITY_ANALYSIS_TYPE;
        }

        return self::PRICING_GRANULARITY_ANALYSIS_TYPE;
    }
};
