<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_ids')) {
                $table->text('subcontracting_dispatch_lab_ids')->nullable()->after('subcontracting_dispatch_date');
            }

            if (! Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_names')) {
                $table->text('subcontracting_dispatch_lab_names')->nullable()->after('subcontracting_dispatch_lab_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_names')) {
                $table->dropColumn('subcontracting_dispatch_lab_names');
            }

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_ids')) {
                $table->dropColumn('subcontracting_dispatch_lab_ids');
            }
        });
    }
};
