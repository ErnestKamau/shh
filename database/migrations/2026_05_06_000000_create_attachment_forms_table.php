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

            $table->index(['attachment_form_id', 'submission_form_template_type_id'], 'attachment_form_template_type_index');
            $table->unique(['attachment_form_id', 'submission_form_template_type_id'], 'attachment_form_template_type_unique');

            $table->foreign('attachment_form_id', 'attachment_form_template_type_attachment_form_id_foreign')
                ->references('id')
                ->on('attachment_forms')
                ->onUpdate('no action')
                ->onDelete('cascade');

            $table->foreign('submission_form_template_type_id', 'attachment_form_template_type_template_type_id_foreign')
                ->references('id')
                ->on('submission_form_template_types')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachment_form_template_type');
        Schema::dropIfExists('attachment_forms');
    }
};
