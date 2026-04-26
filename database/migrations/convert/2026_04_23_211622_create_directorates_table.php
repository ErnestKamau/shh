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
        Schema::create('directorates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->uuid('head_id')->nullable()->index('idx_directorates_head_id_ced2fa93');
            $table->uuid('zone_id')->nullable()->index('idx_directorates_zone_id_0b070a99');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->foreign(['head_id'], 'fk_directorates_head_id_1092a617')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['zone_id'], 'fk_directorates_zone_id_b8b22de7')->references(['id'])->on('zones')->onUpdate('no action')->onDelete('set null');
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
