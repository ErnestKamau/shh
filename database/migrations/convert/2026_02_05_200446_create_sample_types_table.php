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
        if (Schema::hasTable('sample_types')) {
            return;
        }
        Schema::create('sample_types', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code');
            $table->string('name');
            $table->string('description')->nullable();
            $table->uuid('company_id')->index('idx_sample_types_company_id_e33c502b');
            $table->boolean('active')->default(false);
            $table->boolean('is_results_attachable')->default(false);
            $table->timestamps();
            $table->integer('report_template_id')->nullable();
            $table->uuid('report_format_id')->nullable()->index('idx_sample_types_report_format_id_53c43e7e');
            $table->unsignedBigInteger('default_product_id')->nullable();
            $table->integer('disposal_count')->default(0);
            $table->integer('sample_type_category')->default(1);
            $table->boolean('is_external')->nullable()->default(true);
            $table->uuid('rating_header_id')->nullable()->index('idx_sample_types_rating_header_id_8394d726');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_types');
    }
};
