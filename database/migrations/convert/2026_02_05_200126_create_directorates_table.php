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
        if (Schema::hasTable('directorates')) {
            return;
        }
        Schema::create('directorates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->uuid('head_id')->nullable()->index('idx_directorates_head_id_5335257a');
            $table->uuid('zone_id')->nullable()->index('idx_directorates_zone_id_749c0fab');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directorates');
    }
};
