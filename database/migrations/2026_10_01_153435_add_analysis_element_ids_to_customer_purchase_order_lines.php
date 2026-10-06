<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Per-test PO lines cover specific parameters (analysis elements); package lines cover a whole
     * analysis type. Existing lines take their parameters from the quotation line they came from.
     */
    public function up(): void
    {
        Schema::table('customer_purchase_order_lines', function (Blueprint $table) {
            $table->json('analysis_element_ids')->nullable()->after('analysis_type_ids');
        });

        DB::table('customer_purchase_order_lines')
            ->join('quotation_details', 'quotation_details.id', '=', 'customer_purchase_order_lines.quotation_detail_id')
            ->whereNull('customer_purchase_order_lines.analysis_element_ids')
            ->select([
                'customer_purchase_order_lines.id',
                'quotation_details.default_analytes',
                'quotation_details.accredited_analytes',
                'quotation_details.subcontracted_analytes',
                'quotation_details.sub_acc_analytes',
            ])
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $elementIds = $this->elementIds($row->default_analytes);
                    if ($elementIds === []) {
                        $elementIds = $this->elementIds(implode(',', [
                            (string) $row->accredited_analytes,
                            (string) $row->subcontracted_analytes,
                            (string) $row->sub_acc_analytes,
                        ]));
                    }

                    if ($elementIds === []) {
                        continue;
                    }

                    DB::table('customer_purchase_order_lines')
                        ->where('id', $row->id)
                        ->update(['analysis_element_ids' => json_encode($elementIds)]);
                }
            }, 'customer_purchase_order_lines.id', 'id');
    }

    public function down(): void
    {
        Schema::table('customer_purchase_order_lines', function (Blueprint $table) {
            $table->dropColumn('analysis_element_ids');
        });
    }

    /**
     * @return list<string>
     */
    private function elementIds(?string $csv): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string) $csv)),
            static fn (string $id): bool => Str::isUuid($id),
        )));
    }
};
