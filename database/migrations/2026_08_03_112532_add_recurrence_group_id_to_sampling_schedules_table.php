<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sampling_schedules')) {
            return;
        }

        Schema::table('sampling_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('sampling_schedules', 'recurrence_group_id')) {
                $table->uuid('recurrence_group_id')->nullable()->after('frequency')->index();
            }
        });

        $this->backfillExistingRecurringSchedules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sampling_schedules')) {
            return;
        }

        Schema::table('sampling_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('sampling_schedules', 'recurrence_group_id')) {
                $table->dropColumn('recurrence_group_id');
            }
        });
    }

    /**
     * Best-effort grouping of pre-existing recurring occurrences.
     *
     * Recurring occurrences were historically created as independent clones in a
     * single request, so rows sharing company/customer/title/frequency with
     * near-identical created_at timestamps belong to the same series.
     */
    private function backfillExistingRecurringSchedules(): void
    {
        $rows = DB::table('sampling_schedules')
            ->whereNull('recurrence_group_id')
            ->whereNotNull('frequency')
            ->where('frequency', '!=', '')
            ->where('frequency', '!=', 'One-time')
            ->orderBy('created_at')
            ->get(['id', 'company_id', 'crm_customer_id', 'title', 'frequency', 'created_at']);

        $clusters = [];
        foreach ($rows as $row) {
            $key = implode('|', [
                (string) $row->company_id,
                (string) $row->crm_customer_id,
                (string) $row->title,
                (string) $row->frequency,
            ]);

            $createdAt = $row->created_at ? Carbon::parse($row->created_at) : null;
            $cluster = $clusters[$key] ?? null;

            $startsNewCluster = $cluster === null
                || $createdAt === null
                || $cluster['first_created_at'] === null
                || $createdAt->diffInMinutes($cluster['first_created_at']) > 10;

            if ($startsNewCluster) {
                if ($cluster !== null) {
                    $this->assignGroupId($cluster['ids']);
                }

                $clusters[$key] = [
                    'first_created_at' => $createdAt,
                    'ids' => [$row->id],
                ];

                continue;
            }

            $clusters[$key]['ids'][] = $row->id;
        }

        foreach ($clusters as $cluster) {
            $this->assignGroupId($cluster['ids']);
        }
    }

    /**
     * @param  list<string>  $ids
     */
    private function assignGroupId(array $ids): void
    {
        if (count($ids) < 2) {
            return;
        }

        DB::table('sampling_schedules')
            ->whereIn('id', $ids)
            ->update(['recurrence_group_id' => (string) Str::uuid()]);
    }
};
