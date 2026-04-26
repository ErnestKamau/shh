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
        Schema::create('audit_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->uuid('notification_type_id')->nullable()->index('audit_notifications_notification_type_id_foreign');
            $table->string('notification_type_name')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->unsignedBigInteger('recipient_user_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_audit_notifications_company_id_a2bffcdc');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id'], 'idx_audit_notifications_notifiable_type_notifiable_id_f7fe4629');
            $table->index(['recipient_user_id', 'is_read'], 'idx_audit_notifications_recipient_user_id_is_read_0c1d4b16');
            $table->foreign(['notification_type_id'], 'fk_audit_notifications_notification_type_id_d6765bc3')->references(['id'])->on('audit_notification_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_notifications_company_id_45aa558e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
