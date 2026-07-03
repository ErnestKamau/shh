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
        if (Schema::hasTable('audit_notifications')) {
            return;
        }
        Schema::create('audit_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->uuid('notification_type_id')->nullable()->index('idx_audit_notifications_notification_type_id_5cec6fd9');
            $table->string('notification_type_name')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('recipient_user_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_audit_notifications_company_id_3ebd88ad');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id'], 'idx_audit_notifications_notifiable_type_notifiable_id_a81fcaa5');
            $table->index(['recipient_user_id', 'is_read'], 'idx_audit_notifications_recipient_user_id_is_read_23a45416');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_notifications');
    }
};
