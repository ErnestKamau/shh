<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DUBAI_COMPANY_ID = '019dde3f-07d3-73d0-a0f2-a01ac58346b4';

    /**
     * @var list<string>
     */
    private const DUBAI_TRF_DOCUMENT_CODES = [
        'TRF-FOOD-019',
        'TRF-WATER-020',
        'TRF-WASTEWATER-036',
        'TRF-WASTE-036',
        'TRF-FOOD-FEED-021',
        'TRF-SWAB-022',
        'TRF-AMSPEC-001',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('submission_forms') || ! Schema::hasColumn('submission_forms', 'company_id')) {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'company_id')) {
            $forms = DB::table('submission_forms')
                ->whereNull('company_id')
                ->whereNotNull('created_by')
                ->get(['id', 'created_by']);

            foreach ($forms as $form) {
                $companyId = DB::table('users')
                    ->where('id', $form->created_by)
                    ->value('company_id');

                if ($companyId === null || $companyId === '') {
                    continue;
                }

                DB::table('submission_forms')
                    ->where('id', $form->id)
                    ->update(['company_id' => $companyId]);
            }
        }

        if (! Schema::hasTable('companies')) {
            return;
        }

        $dubaiExists = DB::table('companies')->where('id', self::DUBAI_COMPANY_ID)->exists();
        if (! $dubaiExists) {
            return;
        }

        DB::table('submission_forms')
            ->whereNull('company_id')
            ->where(function ($query): void {
                $query->where('document_code', 'like', 'TRF-%')
                    ->orWhereIn('document_code', self::DUBAI_TRF_DOCUMENT_CODES);
            })
            ->update(['company_id' => self::DUBAI_COMPANY_ID]);
    }

    public function down(): void
    {
        // Backfill is not reversed; company_id remains assigned.
    }
};
