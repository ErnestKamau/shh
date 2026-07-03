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
        if (Schema::hasTable('work_order_status_histories')) {
            return;
        }
        Schema::create('work_order_status_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('workorder_id');
            $table->string('status');
            $table->text('comments')->nullable();
            $table->string('created_by');
            $table->uuid('created_by_id')->nullable()->index('idx_work_order_status_histories_created_by_id_29084817');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_status_histories');
    }
};
