<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submission_forms') || ! Schema::hasColumn('submission_forms', 'company_id')) {
            return;
        }

        // 1) Prefer the creator's company when available.
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

        // 2) Remaining rows: System Settings default company (companies.active = 1).
        $defaultCompanyId = $this->resolveSystemSettingsCompanyId();
        if ($defaultCompanyId === null) {
            return;
        }

        DB::table('submission_forms')
            ->whereNull('company_id')
            ->update(['company_id' => $defaultCompanyId]);
    }

    public function down(): void
    {
        // Backfill is not reversed; company_id remains assigned.
    }

    private function resolveSystemSettingsCompanyId(): ?string
    {
        $activeId = DB::table('companies')->where('active', 1)->value('id');
        if ($activeId !== null && $activeId !== '') {
            return (string) $activeId;
        }

        if (Schema::hasTable('system_configurations')) {
            $configured = DB::table('system_configurations')
                ->where('key', 'active_company')
                ->value('value');

            if ($configured !== null && $configured !== ''
                && DB::table('companies')->where('id', $configured)->exists()) {
                return (string) $configured;
            }
        }

        $companyIds = DB::table('companies')->orderBy('id')->limit(2)->pluck('id');
        if ($companyIds->count() === 1) {
            return (string) $companyIds->first();
        }

        return null;
    }
};
