<?php

namespace Database\Seeders\Concerns;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\QuotationDetails;
use App\QuotationHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsSeedCommercialDemoData
{
    public const SEED_PRICELIST_CODE_PREFIX = 'PL-SEED-';

    public const SEED_PRICELIST_CODE_MASTER = 'PL-SEED-MASTER';

    public const SEED_PRICELIST_CODE_CUSTOMER = 'PL-SEED-CUSTOMER';

    public const SEED_PRICELIST_CODE_PACKAGE = 'PL-SEED-PACKAGE';

    public const SEED_QUOTATION_LAB_REF_PREFIX = 'SEED-QTE-';

    protected function clearSeedCommercialDemoData(): void
    {
        $quotationIds = QuotationHeader::query()
            ->where('laboratory_ref', 'like', self::SEED_QUOTATION_LAB_REF_PREFIX.'%')
            ->pluck('id');

        if ($quotationIds->isNotEmpty()) {
            QuotationDetails::query()->whereIn('quotation_header_id', $quotationIds)->delete();
            QuotationHeader::query()->whereIn('id', $quotationIds)->delete();
            $this->command?->info("Cleared {$quotationIds->count()} seed quotation(s).");
        }

        $pricelistIds = Pricelist::query()
            ->where('code', 'like', self::SEED_PRICELIST_CODE_PREFIX.'%')
            ->pluck('id');

        if ($pricelistIds->isEmpty()) {
            return;
        }

        PricelistItem::query()->whereIn('pricelist_id', $pricelistIds)->delete();
        PricelistCustomer::query()->whereIn('pricelist_id', $pricelistIds)->delete();
        $deleted = Pricelist::query()->whereIn('id', $pricelistIds)->delete();

        $this->command?->info("Cleared {$deleted} seed pricelist(s).");
    }

    protected function clearSeedEquipmentMonitoringData(): void
    {
        if (! Schema::connection('pgsql')->hasTable('maintainance_calibration_logs')) {
            return;
        }

        $deletedLogs = DB::connection('pgsql')->table('maintainance_calibration_logs')
            ->where('reference_number', 'like', 'SEED-%')
            ->delete();

        if ($deletedLogs > 0) {
            $this->command?->info("Cleared {$deletedLogs} seed equipment maintenance/calibration log(s).");
        }

        if (! Schema::connection('pgsql')->hasTable('equipment_daily_log_entries')) {
            return;
        }

        $deletedDaily = DB::connection('pgsql')->table('equipment_daily_log_entries')
            ->where('recorded_value', 'like', 'SEED-%')
            ->delete();

        if ($deletedDaily > 0) {
            $this->command?->info("Cleared {$deletedDaily} seed equipment daily log entr(ies).");
        }
    }

    protected function clearSeedCalendarPlannerData(): void
    {
        if (! Schema::connection('pgsql')->hasTable('calendar_events')) {
            return;
        }

        $deleted = DB::connection('pgsql')->table('calendar_events')
            ->where('title', 'like', 'SEED-%')
            ->delete();

        if ($deleted > 0) {
            $this->command?->info("Cleared {$deleted} seed calendar event(s).");
        }
    }
}
