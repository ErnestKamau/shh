<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add form_type column to submission_forms (default 'template' for existing rows)
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->string('form_type', 20)->default('template')->after('template_form_type_id');
        });

        // 2. Self-referencing pivot: attachment forms → template forms
        Schema::create('submission_form_template_links', function (Blueprint $table) {
            $table->uuid('attachment_form_id');
            $table->uuid('template_form_id');
            $table->timestamps();

            $table->unique(['attachment_form_id', 'template_form_id'], 'sf_template_links_unique');
            $table->index('attachment_form_id', 'sf_template_links_af_index');
            $table->index('template_form_id', 'sf_template_links_tf_index');

            $table->foreign('attachment_form_id', 'sf_template_links_af_foreign')
                ->references('id')
                ->on('submission_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('template_form_id', 'sf_template_links_tf_foreign')
                ->references('id')
                ->on('submission_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });

        // 3. Drop old attachment_form_* tables (order matters: pivots before parent)
        Schema::dropIfExists('attachment_form_sample_types');
        Schema::dropIfExists('attachment_form_customers');
        Schema::dropIfExists('attachment_form_template_type');
        Schema::dropIfExists('attachment_forms');
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_template_links');

        Schema::table('submission_forms', function (Blueprint $table) {
            $table->dropColumn('form_type');
        });

        // Restore attachment_forms and its pivots
        Schema::create('attachment_forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('attachment_form_template_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attachment_form_id');
            $table->unsignedBigInteger('submission_form_template_type_id');
            $table->timestamps();
        });

        Schema::create('attachment_form_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('attachment_form_id');
            $table->uuid('crm_customer_id');
            $table->timestamps();
        });

        Schema::create('attachment_form_sample_types', function (Blueprint $table) {
            $table->unsignedBigInteger('attachment_form_id');
            $table->uuid('sample_type_id');
            $table->timestamps();
        });
    }
};
