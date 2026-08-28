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

    /**
     * Delete one pricelist and its dependent catalogue rows.
     * Detaches foreign references on quotations / invoices / acceptance forms.
     *
     * @return array{code: string, items: int, customers: int, email_logs: int}
     */
    public function deleteOne(string $pricelistId): array
    {
        $pricelistId = trim($pricelistId);
        if ($pricelistId === '') {
            throw new \InvalidArgumentException('Pricelist id is required.');
        }

        return DB::transaction(function () use ($pricelistId): array {
            $pricelist = DB::table('pricelists')->where('id', $pricelistId)->first();
            if ($pricelist === null) {
                throw new \RuntimeException('Pricelist not found.');
            }

            $this->nullColumnForId('quotation_headers', 'pricelist_id', $pricelistId);
            $this->nullColumnForId('customer_invoice', 'pricelist_id', $pricelistId);
            $this->nullColumnForId('analysis_acceptance_forms', 'pricelist_id', $pricelistId);

            $itemIds = DB::table('pricelist_items')
                ->where('pricelist_id', $pricelistId)
                ->pluck('id')
                ->all();

            if ($itemIds !== [] && Schema::hasTable('pricelist_item_elements')) {
                DB::table('pricelist_item_elements')->whereIn('pricelist_item_id', $itemIds)->delete();
            }

            $items = Schema::hasTable('pricelist_items')
                ? (int) DB::table('pricelist_items')->where('pricelist_id', $pricelistId)->delete()
                : 0;
            $customers = Schema::hasTable('pricelist_customers')
                ? (int) DB::table('pricelist_customers')->where('pricelist_id', $pricelistId)->delete()
                : 0;
            $emailLogs = Schema::hasTable('pricelist_email_logs')
                ? (int) DB::table('pricelist_email_logs')->where('pricelist_id', $pricelistId)->delete()
                : 0;

            DB::table('pricelists')->where('id', $pricelistId)->delete();

            return [
                'code' => (string) ($pricelist->code ?? $pricelistId),
                'items' => $items,
                'customers' => $customers,
                'email_logs' => $emailLogs,
            ];
        });
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

    private function nullColumnForId(string $table, string $column, string $pricelistId): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        if (! $this->columnIsNullable($table, $column)) {
            $this->dropNotNull($table, $column);
        }

        return (int) DB::table($table)->where($column, $pricelistId)->update([$column => null]);
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
