<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'quotation_content_stale_at')) {
                $table->timestamp('quotation_content_stale_at')->nullable()->after('quotation_first_sent_to_customer_at');
            }
        });

        // Align legacy Billing email sends with the clearer delivery stamp.
        if (Schema::hasColumn('quotation_headers', 'sent_to_customer_at')
            && Schema::hasColumn('quotation_headers', 'email_to_customer')) {
            \Illuminate\Support\Facades\DB::table('quotation_headers')
                ->whereNotNull('email_to_customer')
                ->whereNull('sent_to_customer_at')
                ->update(['sent_to_customer_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'quotation_content_stale_at')) {
                $table->dropColumn('quotation_content_stale_at');
            }
        });
    }
};
