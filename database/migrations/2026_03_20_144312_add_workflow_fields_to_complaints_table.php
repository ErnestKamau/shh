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
            $table->string('mode_of_delivery')->nullable();
            $table->string('received_by')->nullable();
            $table->string('received_from_type')->nullable();
            $table->boolean('is_lab_related')->default(false);
            $table->text('nature_of_complaint')->nullable();
            $table->string('test_item_report_serial_no')->nullable();
            
            $table->bigInteger('intake_approved_by')->nullable();
            $table->foreign('intake_approved_by')->references('id')->on('users')->nullOnDelete();
            
            $table->bigInteger('closed_by')->nullable();
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
            
            $table->date('date_closed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['intake_approved_by']);
            $table->dropForeign(['closed_by']);
            $table->dropColumn([
                'mode_of_delivery',
                'received_by',
                'received_from_type',
                'is_lab_related',
                'nature_of_complaint',
                'test_item_report_serial_no',
                'intake_approved_by',
                'closed_by',
                'date_closed'
            ]);
        });
    }
};
