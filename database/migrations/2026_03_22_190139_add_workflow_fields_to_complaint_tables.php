<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add intake approval timestamp to complaints
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'intake_approved_at')) {
                $table->timestamp('intake_approved_at')->nullable()->after('intake_approved_by');
            }
        });

        // Add CAPA/NCR workflow fields to complaintsresolutions
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            if (!Schema::hasColumn('complaintsresolutions', 'ncr_required')) {
                $table->boolean('ncr_required')->default(false)->after('car_required');
            }
            if (!Schema::hasColumn('complaintsresolutions', 'capa_approved_by')) {
                $table->unsignedBigInteger('capa_approved_by')->nullable()->after('car_type');
            }
            if (!Schema::hasColumn('complaintsresolutions', 'capa_approved_at')) {
                $table->timestamp('capa_approved_at')->nullable()->after('capa_approved_by');
            }
            if (!Schema::hasColumn('complaintsresolutions', 'send_to_customer')) {
                $table->boolean('send_to_customer')->default(false)->after('client_remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'intake_approved_at')) {
                $table->dropColumn('intake_approved_at');
            }
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $cols = ['ncr_required', 'capa_approved_by', 'capa_approved_at', 'send_to_customer'];
            $columnsToDrop = [];
            foreach ($cols as $col) {
                if (Schema::hasColumn('complaintsresolutions', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
