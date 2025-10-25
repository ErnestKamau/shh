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
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->boolean('has_method_sequence')->default(false)->after('formular_id');
            $table->unsignedBigInteger('method_sequence_id')->nullable()->after('has_method_sequence');
            
            $table->index('method_sequence_id');
            $table->foreign('method_sequence_id')
                ->references('id')
                ->on('method_sequences')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropForeign(['method_sequence_id']);
            $table->dropIndex(['method_sequence_id']);
            $table->dropColumn(['has_method_sequence', 'method_sequence_id']);
        });
    }
};
