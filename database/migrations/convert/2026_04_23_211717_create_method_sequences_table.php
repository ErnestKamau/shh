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
        Schema::create('method_sequences', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('analyte_id')->index('idx_method_sequences_analyte_id_5ca33278');
            $table->unsignedBigInteger('method_id')->index('idx_method_sequences_method_id_0acb7165');
            $table->boolean('is_active')->default(true)->index('idx_method_sequences_is_active_d0fd1ed8');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'deleted_at']);
            $table->foreign(['analyte_id'], 'fk_method_sequences_analyte_id_99ba4b3b')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequences');
    }
};
