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
        Schema::create('sample_header_staging', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_header_id')->nullable()->index('idx_sample_header_staging_sample_header_id_8b04e01d');
            $table->uuid('sample_type_id')->nullable()->index('idx_sample_header_staging_sample_type_id_d996c49a');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('book_date');
            $table->string('book_no');
            $table->integer('created_by');
            $table->integer('status');
            $table->text('excel_url')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('crm_unit_id')->nullable();
            $table->date('date_collected')->nullable();
            $table->date('receipt_date')->nullable();
            $table->time('receipt_time')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->integer('cancelled_by')->nullable();
            $table->foreign(['sample_type_id'], 'fk_sample_header_staging_sample_type_id_ac1236b3')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_header_staging');
    }
};
