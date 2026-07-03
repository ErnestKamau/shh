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
        if (Schema::hasTable('verification_logs')) {
            return;
        }
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->date('verification_date');
            $table->text('procedure');
            $table->string('reference_standard');
            $table->text('response');
            $table->text('remarks');
            $table->integer('operator_id');
            $table->integer('edit_by')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->uuid('equipment_id')->nullable()->index('idx_verification_logs_equipment_id_f4b0f0d4');
            $table->timestamps();
            $table->string('maintainance_type', 100)->nullable();
            $table->uuid('supplier_id')->nullable()->index('idx_verification_logs_supplier_id_0d70457d');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
