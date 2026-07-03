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
            $table->uuid('id')->index('idx_batch_ammendments_id_c5047fc3');
            $table->timestamps();
            $table->uuid('batch_id')->index('idx_batch_ammendments_batch_id_f39fba27');
            $table->uuid('created_by_id')->nullable()->index('idx_batch_ammendments_created_by_id_79012d40');
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
