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
        Schema::table('method_sequence_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('analyst_id')->nullable()->after('method_sequence_id');
            $table->date('run_date')->nullable()->after('analyst_id');
            
            $table->foreign('analyst_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('method_sequence_runs', function (Blueprint $table) {
            $table->dropForeign(['analyst_id']);
            $table->dropColumn(['analyst_id', 'run_date']);
        });
    }
};
