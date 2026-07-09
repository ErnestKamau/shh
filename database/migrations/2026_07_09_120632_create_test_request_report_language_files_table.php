<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_request_report_language_files', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id');
            $table->unsignedSmallInteger('revision_no');
            $table->string('language', 10);
            $table->string('report_url')->nullable();
            $table->text('report_online_url')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'revision_no', 'language'], 'trr_lang_files_batch_rev_lang_unique');
            $table->index(['batch_id', 'revision_no'], 'trr_lang_files_batch_rev_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_request_report_language_files');
    }
};
