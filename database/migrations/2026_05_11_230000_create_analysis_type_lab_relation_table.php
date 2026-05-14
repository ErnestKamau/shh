<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_type_lab_relation', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('analysis_type_id')->constrained('analysis_types')->cascadeOnDelete();
            $table->foreignUuid('lab_id')->constrained('labs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['analysis_type_id', 'lab_id'], 'analysis_type_lab_relation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_type_lab_relation');
    }
};
