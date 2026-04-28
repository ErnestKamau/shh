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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->default('form');
            $table->string('status')->default('draft');
            $table->longText('header_content')->nullable();
            $table->longText('footer_content')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_form_templates_created_by_76313e2a');
            $table->timestamps();
            $table->softDeletes();
            $table->string('process_type')->nullable();
            $table->unsignedBigInteger('process_id')->nullable();

            $table->index(['process_type', 'process_id'], 'idx_form_templates_process_type_process_id_b0d8a1c4');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};
