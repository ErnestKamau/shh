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
        if (Schema::hasTable('sample_header_staging')) {
            return;
        }
        Schema::create('sample_header_staging', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_header_id')->nullable()->index('idx_sample_header_staging_sample_header_id_d8a80faa');
            $table->uuid('sample_type_id')->nullable()->index('idx_sample_header_staging_sample_type_id_222c07c9');
            $table->dateTime('deleted_at')->nullable();
            $table->dateTime('book_date');
            $table->string('book_no');
            $table->uuid('created_by')->nullable()->index('idx_sample_header_staging_created_by_7446505e');
            $table->integer('status');
            $table->text('excel_url')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('crm_unit_id')->nullable();
            $table->date('date_collected')->nullable();
            $table->date('receipt_date')->nullable();
            $table->time('receipt_time')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->integer('cancelled_by')->nullable();

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
