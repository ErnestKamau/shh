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
        if (Schema::hasTable('certificate_template_element_holders')) {
            return;
        }
        Schema::create('certificate_template_element_holders', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('certificate_template_section_id')->index('idx_certificate_template_element_holders_certificate_t_ef895c0c');
            $table->uuid('parent_holder_id')->nullable()->index('idx_certificate_template_element_holders_parent_holder_e488bda7');
            $table->enum('holder_type', ['field', 'text'])->default('field');
            $table->enum('direction', ['horizontal', 'vertical'])->default('horizontal');
            $table->enum('data_source', ['Company', 'CRMCustomer', 'SampleHeader', 'SampleDetails', 'CapturedResult'])->nullable();
            $table->longText('field_mappings')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('form_number')->nullable();
            $table->date('publish_date')->nullable();
            $table->string('qa_other_details')->nullable();
            $table->integer('max_elements')->default(10);
            $table->integer('sort_order')->default(0);
            $table->decimal('position_x', 10)->nullable();
            $table->decimal('position_y', 10)->nullable();
            $table->decimal('width', 10)->nullable();
            $table->decimal('height', 10)->nullable();
            $table->decimal('position_x_percent', 10, 4)->nullable();
            $table->decimal('position_y_percent', 10, 4)->nullable();
            $table->decimal('width_percent', 10, 4)->nullable();
            $table->decimal('height_percent', 10, 4)->nullable();
            $table->decimal('flex_grow', 5)->nullable()->default(0);
            $table->decimal('flex_shrink', 5)->nullable()->default(1);
            $table->string('flex_basis', 50)->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_element_holders');
    }
};
