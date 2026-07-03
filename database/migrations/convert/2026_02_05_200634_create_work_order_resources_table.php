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
        if (Schema::hasTable('work_order_resources')) {
            return;
        }
        Schema::create('work_order_resources', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('resource_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('type');
            $table->integer('workorder_id');
            $table->timestamps();
            $table->boolean('is_external')->default(false);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_resources');
    }
};
