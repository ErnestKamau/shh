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
        if (Schema::hasTable('supplier_r_f_q_s')) {
            return;
        }
        Schema::create('supplier_r_f_q_s', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_r_f_q_s_supplier_id_18ccd372');
            $table->integer('request_id');
            $table->boolean('rfq_sent')->default(false);
            $table->boolean('quote_received')->default(false);
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_r_f_q_s');
    }
};
