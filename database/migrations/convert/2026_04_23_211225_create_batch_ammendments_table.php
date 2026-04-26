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
        Schema::create('batch_ammendments', function (Blueprint $table) {
            $table->uuid('id')->index('id');
            $table->timestamps();
            $table->integer('batch_id')->index('batch_id');
            $table->integer('created_by_id');
            $table->text('reason');
            $table->string('samples');
            $table->string('report_url');
            $table->integer('version_number')->default(0);

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_ammendments');
    }
};
