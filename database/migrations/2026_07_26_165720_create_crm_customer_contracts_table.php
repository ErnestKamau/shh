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
        Schema::create('crm_customer_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('crm_customer_id')->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('original_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_extension', 50)->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('posted_by')->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('crm_customer_id')
                ->references('id')
                ->on('crm_customers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_customer_contracts');
    }
};
