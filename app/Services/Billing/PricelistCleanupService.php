<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PricelistCleanupService
{
    /**
     * Delete all pricelist catalogue data. Does not touch sample types, analysis,
     * users, TRFs, or other operational master data.
     *
     * @return array<string, int>
     */
    public function deleteAll(): array
    {
        $counts = [];

        $counts['quotation_headers.pricelist_id'] = $this->nullColumn('quotation_headers', 'pricelist_id');
        $counts['customer_invoice.pricelist_id'] = $this->nullColumn('customer_invoice', 'pricelist_id');
        $counts['analysis_acceptance_forms.pricelist_id'] = $this->nullColumn('analysis_acceptance_forms', 'pricelist_id');

        foreach ([
            'pricelist_item_elements',
            'pricelist_items',
            'pricelist_customers',
            'pricelist_email_logs',
            'pricelists',
        ] as $table) {
            $counts[$table] = $this->deleteAllRows($table);
        }

        return $counts;
    }

    private function nullColumn(string $table, string $column): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        if (! $this->columnIsNullable($table, $column)) {
            $this->dropNotNull($table, $column);
        }

        return (int) DB::table($table)->whereNotNull($column)->update([$column => null]);
    }

    private function deleteAllRows(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->delete();
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        if (DB::getDriverName() !== 'pgsql') {
            return true;
        }

        $row = DB::selectOne(
            'SELECT is_nullable FROM information_schema.columns WHERE table_name = ? AND column_name = ? LIMIT 1',
            [$table, $column]
        );

        return $row !== null && strtoupper((string) ($row->is_nullable ?? '')) === 'YES';
    }

    private function dropNotNull(string $table, string $column): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(sprintf('ALTER TABLE %s ALTER COLUMN %s DROP NOT NULL', $table, $column));
        }
    }
}
