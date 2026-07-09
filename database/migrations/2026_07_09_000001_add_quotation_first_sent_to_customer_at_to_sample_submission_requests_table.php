<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'quotation_first_sent_to_customer_at')) {
                $table->timestamp('quotation_first_sent_to_customer_at')
                    ->nullable()
                    ->after('quotation_accepted_at');
            }
        });

        if (! Schema::hasTable('quotation_headers')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('
                UPDATE sample_submission_requests AS ssr
                SET quotation_first_sent_to_customer_at = earliest.sent_at
                FROM (
                    SELECT sample_submission_request_id, MIN(sent_to_customer_at) AS sent_at
                    FROM quotation_headers
                    WHERE sent_to_customer_at IS NOT NULL
                      AND sample_submission_request_id IS NOT NULL
                    GROUP BY sample_submission_request_id
                ) AS earliest
                WHERE ssr.id = earliest.sample_submission_request_id
                  AND ssr.quotation_first_sent_to_customer_at IS NULL
            ');
        } else {
            DB::statement('
                UPDATE sample_submission_requests AS ssr
                INNER JOIN (
                    SELECT sample_submission_request_id, MIN(sent_to_customer_at) AS sent_at
                    FROM quotation_headers
                    WHERE sent_to_customer_at IS NOT NULL
                      AND sample_submission_request_id IS NOT NULL
                    GROUP BY sample_submission_request_id
                ) AS earliest ON ssr.id = earliest.sample_submission_request_id
                SET ssr.quotation_first_sent_to_customer_at = earliest.sent_at
                WHERE ssr.quotation_first_sent_to_customer_at IS NULL
            ');
        }

        if (! Schema::hasColumn('crm_customers', 'quotation_acceptance_tat_minutes')) {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('
                UPDATE crm_customers AS c
                SET quotation_acceptance_tat_minutes = NULL
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM sample_submission_requests AS ssr
                    WHERE ssr.crm_customer_id::text = c.id::text
                      AND ssr.quotation_accepted_at IS NOT NULL
                )
            ');

            DB::statement('
                UPDATE crm_customers AS c
                SET quotation_acceptance_tat_minutes = latest.tat_minutes
                FROM (
                    SELECT DISTINCT ON (ssr.crm_customer_id)
                        ssr.crm_customer_id,
                        GREATEST(
                            EXTRACT(EPOCH FROM (ssr.quotation_accepted_at - COALESCE(
                                ssr.quotation_first_sent_to_customer_at,
                                qh.earliest_sent
                            )))::integer / 60,
                            0
                        ) AS tat_minutes
                    FROM sample_submission_requests AS ssr
                    LEFT JOIN LATERAL (
                        SELECT MIN(sent_to_customer_at) AS earliest_sent
                        FROM quotation_headers
                        WHERE sample_submission_request_id = ssr.id
                          AND sent_to_customer_at IS NOT NULL
                    ) AS qh ON TRUE
                    WHERE ssr.quotation_accepted_at IS NOT NULL
                      AND ssr.crm_customer_id IS NOT NULL
                    ORDER BY ssr.crm_customer_id, ssr.quotation_accepted_at DESC
                ) AS latest
                WHERE c.id::text = latest.crm_customer_id::text
            ');
        } else {
            DB::table('crm_customers')
                ->whereNotExists(function ($query): void {
                    $query->select(DB::raw(1))
                        ->from('sample_submission_requests as ssr')
                        ->whereColumn('ssr.crm_customer_id', 'crm_customers.id')
                        ->whereNotNull('ssr.quotation_accepted_at');
                })
                ->update(['quotation_acceptance_tat_minutes' => null]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'quotation_first_sent_to_customer_at')) {
                $table->dropColumn('quotation_first_sent_to_customer_at');
            }
        });
    }
};
