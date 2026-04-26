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
        Schema::create('report_table_configurations', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name', 64);
            $table->text('configuration')->nullable();
            $table->text('raw_sql')->nullable();
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
            $table->string('report_module', 100)->default('inventory');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_table_configurations');
    }
};
