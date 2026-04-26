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
            $table->uuid('equipment_id')->nullable()->index('idx_verification_logs_equipment_id_a1b75f99');
            $table->timestamps();
            $table->string('maintainance_type', 100)->nullable();
            $table->uuid('supplier_id')->nullable()->index('idx_verification_logs_supplier_id_d7a87675');
            $table->foreign(['equipment_id'], 'fk_verification_logs_equipment_id_15b33975')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['supplier_id'], 'fk_verification_logs_supplier_id_4fd7ad25')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('set null');


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
