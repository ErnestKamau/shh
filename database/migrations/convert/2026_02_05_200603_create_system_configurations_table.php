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
        if (Schema::hasTable('system_configurations')) {
            return;
        }
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('configuration_type_id')
                ->nullable();
            $table->text('value');
            $table->string('key');
            $table->boolean('status')->default(false);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
