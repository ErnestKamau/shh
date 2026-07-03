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
        if (Schema::hasTable('topologies')) {
            return;
        }
        Schema::create('topologies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->integer('parent')->default(0);
            $table->integer('level')->default(1);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topologies');
    }
};
