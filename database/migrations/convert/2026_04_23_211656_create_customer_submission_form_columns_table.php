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
        Schema::create('customer_submission_form_columns', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('crm_customer_id')->index('idx_customer_submission_form_columns_crm_customer_id_52e1ca60');
            $table->string('source', 50);
            $table->string('source_key', 100);
            $table->string('display_label', 200)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['crm_customer_id', 'is_active'], 'idx_customer_submission_form_columns_crm_customer_id_i_38379302');
            $table->foreign(['crm_customer_id'], 'fk_customer_submission_form_columns_crm_customer_id_8391a076')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_submission_form_columns');
    }
};
