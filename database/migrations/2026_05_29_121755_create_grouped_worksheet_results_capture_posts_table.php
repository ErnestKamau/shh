<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grouped_worksheet_results_capture_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_header_id');
            $table->uuid('grouped_worksheet_holder_id');
            $table->timestamp('posted_at');
            $table->uuid('posted_by_user_id')->nullable();
            $table->timestamps();

            $table->unique(
                ['sample_header_id', 'grouped_worksheet_holder_id'],
                'gw_results_capture_posts_unique'
            );
            $table->index(['sample_header_id', 'grouped_worksheet_holder_id'], 'gw_results_capture_posts_batch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grouped_worksheet_results_capture_posts');
    }
};
