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
        if (Schema::hasTable('risk_notifications')) {
            return;
        }
        Schema::create('risk_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('notifiable_type');
            $table->uuid('notifiable_id');
            $table->string('title');
            $table->text('message')->nullable();
            $table->uuid('recipient_user_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_risk_notifications_company_id_6661d778');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id'], 'idx_risk_notifications_notifiable_type_notifiable_id_6946ca91');
            $table->index(['recipient_user_id', 'is_read'], 'idx_risk_notifications_recipient_user_id_is_read_c9b30d16');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_notifications');
    }
};
