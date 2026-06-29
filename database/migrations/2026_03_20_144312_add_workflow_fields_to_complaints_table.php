<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'mode_of_delivery')) {
                $table->string('mode_of_delivery')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'received_by')) {
                $table->string('received_by')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'received_from_type')) {
                $table->string('received_from_type')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'is_lab_related')) {
                $table->boolean('is_lab_related')->default(false);
            }
            if (!Schema::hasColumn('complaints', 'nature_of_complaint')) {
                $table->text('nature_of_complaint')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'test_item_report_serial_no')) {
                $table->string('test_item_report_serial_no')->nullable();
            }
            
            if (!Schema::hasColumn('complaints', 'intake_approved_by')) {
                $table->uuid('intake_approved_by')->nullable();
                $table->foreign('intake_approved_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('complaints', 'closed_by')) {
                $table->uuid('closed_by')->nullable();
                $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('complaints', 'date_closed')) {
                $table->date('date_closed')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'intake_approved_by')) {
                try {
                    $table->dropForeign(['intake_approved_by']);
                } catch (\Exception $e) {}
            }
            if (Schema::hasColumn('complaints', 'closed_by')) {
                try {
                    $table->dropForeign(['closed_by']);
                } catch (\Exception $e) {}
            }
            
            $columnsToDrop = [];
            foreach ([
                'mode_of_delivery',
                'received_by',
                'received_from_type',
                'is_lab_related',
                'nature_of_complaint',
                'test_item_report_serial_no',
                'intake_approved_by',
                'closed_by',
                'date_closed'
            ] as $col) {
                if (Schema::hasColumn('complaints', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
