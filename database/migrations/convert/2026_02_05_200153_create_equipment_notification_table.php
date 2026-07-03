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
        if (Schema::hasTable('equipment_notification')) {
            return;
        }
        Schema::create('equipment_notification', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('equipment_id')->index('idx_equipment_notification_equipment_id_a867c54b');
            $table->string('frequency');
            $table->string('value');
            $table->string('next_date');
            $table->dateTime('is_sent')->nullable();
            $table->string('notification_type')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_notification');
    }
};
