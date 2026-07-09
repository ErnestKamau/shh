<?php

use App\Services\Commercial\QuotationAcceptanceTatService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        $service = app(QuotationAcceptanceTatService::class);

        DB::table('crm_customers')
            ->orderBy('id')
            ->pluck('id')
            ->each(static function (string $crmCustomerId) use ($service): void {
                $service->recalculateCustomerTat($crmCustomerId);
            });
    }

    public function down(): void
    {
        // Non-reversible: prior per-customer values are not preserved.
    }
};
