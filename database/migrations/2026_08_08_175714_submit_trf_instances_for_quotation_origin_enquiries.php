<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submission_form_instances') || ! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        $pipelineStatuses = [
            'Requested',
            'Quotation In Progress',
            'Quotation Pending Approval',
            'Quotation Ready to Send',
            'Quotation Sent',
            'Quotation Under Review',
            'Quotation Accepted',
        ];

        $driver = DB::getDriverName();
        $now = now();

        $query = DB::table('submission_form_instances as sfi')
            ->join('sample_submission_requests as ssr', function ($join) use ($driver): void {
                $join->where(function ($link) use ($driver): void {
                    $link->whereColumn('ssr.submission_form_instance_id', 'sfi.id');
                    if ($driver === 'pgsql') {
                        $link->orWhereRaw('ssr.id::text = sfi.portal_request_id');
                    } else {
                        $link->orWhereColumn('ssr.id', 'sfi.portal_request_id');
                    }
                });
            })
            ->whereIn('ssr.status', $pipelineStatuses)
            ->whereIn('sfi.status', ['draft', 'Draft'])
            ->select('sfi.id')
            ->distinct();

        foreach ($query->pluck('id') as $instanceId) {
            DB::table('submission_form_instances')
                ->where('id', $instanceId)
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => $now,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // Irreversible: cannot know which rows were draft before backfill.
    }
};
