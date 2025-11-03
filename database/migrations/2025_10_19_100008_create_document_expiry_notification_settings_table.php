<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentExpiryNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('document_expiry_notification_settings', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('user_id')->unique();
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('app_notifications_enabled')->default(true);
            $table->integer('notification_frequency_days')->default(7)->comment('Days before expiry to send notification');
            $table->json('notification_times')->nullable()->comment('Times when notifications should be sent');
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('document_expiry_notification_settings');
    }
}

