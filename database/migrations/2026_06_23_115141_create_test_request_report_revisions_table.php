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
        Schema::create('test_request_report_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id');
            $table->unsignedSmallInteger('revision_no');
            $table->string('language', 10)->default('en');   // en | ar | pt
            $table->text('notes')->nullable();
            $table->uuid('generated_by')->nullable();
            $table->timestamps();

            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_request_report_revisions');
    }
};
