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
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->string('issued_to')->nullable();
            $table->string('issued_by')->nullable();
            $table->date('date_issued')->nullable();
            $table->date('proposed_close_out_date')->nullable();
            $table->string('ref_clause')->nullable();
            $table->string('car_type')->nullable(); // Major/Minor
            $table->string('risk_level')->nullable(); // High/Medium/Low
            $table->string('capa_identified_by')->nullable();
            $table->date('capa_identified_date')->nullable();
        });

        Schema::table('capa_records', function (Blueprint $table) {
            $table->string('lab_no')->nullable();
            $table->date('ncr_identified_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->dropColumn([
                'issued_to', 'issued_by', 'date_issued', 'proposed_close_out_date',
                'ref_clause', 'car_type', 'risk_level', 'capa_identified_by', 'capa_identified_date'
            ]);
        });

        Schema::table('capa_records', function (Blueprint $table) {
            $table->dropColumn(['lab_no', 'ncr_identified_date']);
        });
    }
};
