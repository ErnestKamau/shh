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
        Schema::create('submission_form_template_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('submission_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('template_form_type_id')->nullable()->after('print_template_name');
            $table->index('template_form_type_id', 'submission_forms_template_form_type_id_index');
            $table->foreign('template_form_type_id', 'submission_forms_template_form_type_id_foreign')
                ->references('id')
                ->on('submission_form_template_types')
                ->onUpdate('no action')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            $table->dropForeign('submission_forms_template_form_type_id_foreign');
            $table->dropIndex('submission_forms_template_form_type_id_index');
            $table->dropColumn('template_form_type_id');
        });

        Schema::dropIfExists('submission_form_template_types');
    }
};
