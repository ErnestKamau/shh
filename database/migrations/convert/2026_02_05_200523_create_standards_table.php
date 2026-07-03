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
        if (Schema::hasTable('standards')) {
            return;
        }
        Schema::create('standards', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('code');
            $table->string('name');
            $table->boolean('status')->default(false);
            $table->integer('edited_by')->nullable();
            $table->boolean('main_standard')->nullable()->default(false);
            $table->boolean('is_qc_standard')->nullable()->default(false);
            $table->uuid('qc_type_id')->nullable()->index('idx_standards_qc_type_id_4ca6c2f2');
            $table->string('qc_scheme_ids', 100)->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standards');
    }
};
