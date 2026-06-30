<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')) {
                $table->string('subcontracting_dispatch_status')->nullable()->after('quotation_accepted_at');
            }

            if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_date')) {
                $table->timestamp('subcontracting_dispatch_date')->nullable()->after('subcontracting_dispatch_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_date')) {
                $table->dropColumn('subcontracting_dispatch_date');
            }

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')) {
                $table->dropColumn('subcontracting_dispatch_status');
            }
        });
    }
};