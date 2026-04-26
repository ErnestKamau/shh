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
        Schema::create('document_expiry_notification_settings', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id')->index('idx_document_expiry_notification_settings_user_id_495ace5a');
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('app_notifications_enabled')->default(true);
            $table->integer('notification_frequency_days')->default(7)->comment('Days before expiry to send notification');
            $table->longText('notification_times')->nullable()->comment('Times when notifications should be sent');
            $table->timestamps();

            $table->unique(['user_id']);
            $table->foreign(['user_id'], 'fk_document_expiry_notification_settings_user_id_9c49d225')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_expiry_notification_settings');
    }
};
