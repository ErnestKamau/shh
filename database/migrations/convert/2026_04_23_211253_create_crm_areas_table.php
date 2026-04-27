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
        Schema::create('crm_areas', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->uuid('created_by')->nullable()->index('idx_crm_areas_created_by');
            $table->timestamps();
            $table->foreign(['created_by'], 'fk_crm_areas_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_areas');
    }
};
