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
        Schema::table('results', function (Blueprint $table) {
            $table->text('sample_detail_code')->change();
            $table->text('analyte_code')->change();
            $table->text('result')->nullable()->change();
            $table->text('guide')->nullable()->change();
            $table->text('comments')->nullable()->change();
            $table->text('recommendations')->nullable()->change();
            $table->text('remarks')->nullable()->change();
            $table->text('scienctific_result')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('sample_detail_code', 255)->change();
            $table->string('analyte_code', 255)->change();
            $table->string('result', 100)->nullable()->change();
            $table->string('guide', 255)->nullable()->change();
            $table->string('comments', 255)->nullable()->change();
            $table->string('recommendations', 255)->nullable()->change();
            $table->string('remarks', 255)->nullable()->change();
            $table->string('scienctific_result', 255)->nullable()->change();
        });
    }
};
