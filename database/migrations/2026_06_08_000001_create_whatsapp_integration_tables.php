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
        // 1. tenant_whatsapp_accounts
        if (!Schema::hasTable('tenant_whatsapp_accounts')) {
            Schema::create('tenant_whatsapp_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('business_name');
                $table->string('phone_number');
                $table->string('phone_number_id');
                $table->string('waba_id');
                $table->string('business_id')->nullable();
                $table->text('access_token');
                $table->string('webhook_verify_token')->nullable();
                $table->string('status', 30)->default('ACTIVE');
                $table->string('quality_rating', 30)->default('UNKNOWN');
                $table->string('messaging_limit', 50)->default('UNKNOWN');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // 2. message_templates
        if (!Schema::hasTable('message_templates')) {
            Schema::create('message_templates', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('name');
                $table->string('category', 50); // MARKETING, UTILITY, AUTHENTICATION
                $table->string('language', 10);
                $table->string('header_type', 20)->default('NONE'); // TEXT, IMAGE, DOCUMENT, VIDEO, NONE
                $table->text('header_content')->nullable();
                $table->text('body_content');
                $table->text('footer_content')->nullable();
                $table->json('buttons_json')->nullable();
                $table->json('variables_json')->nullable();
                $table->string('meta_template_id')->nullable()->index();
                $table->string('status', 30)->default('DRAFT'); // DRAFT, PENDING, APPROVED, REJECTED, DISABLED
                $table->text('rejection_reason')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // 3. event_message_mappings
        if (!Schema::hasTable('event_message_mappings')) {
            Schema::create('event_message_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('event_code', 100)->index();
                $table->unsignedBigInteger('template_id')->index();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // 4. message_schedules
        if (!Schema::hasTable('message_schedules')) {
            Schema::create('message_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->unsignedBigInteger('template_id')->index();
                $table->string('name');
                $table->string('frequency', 30); // ONCE, HOURLY, DAILY, WEEKLY, MONTHLY, CUSTOM_CRON
                $table->string('cron_expression')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->dateTime('next_run_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // 5. message_audiences
        if (!Schema::hasTable('message_audiences')) {
            Schema::create('message_audiences', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('name');
                $table->json('rules_json');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // 6. message_campaigns
        if (!Schema::hasTable('message_campaigns')) {
            Schema::create('message_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('name');
                $table->unsignedBigInteger('template_id')->index();
                $table->unsignedBigInteger('audience_id')->index();
                $table->unsignedBigInteger('schedule_id')->nullable()->index();
                $table->string('status', 30)->default('DRAFT'); // DRAFT, SCHEDULED, RUNNING, COMPLETED, FAILED, PAUSED
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();
            });
        }

        // 7. whatsapp_webhook_events
        if (!Schema::hasTable('whatsapp_webhook_events')) {
            Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->json('payload_json');
                $table->string('event_type', 50)->nullable()->index();
                $table->boolean('processed')->default(false)->index();
                $table->timestamps();
            });
        }

        // 8. whatsapp_activity_logs
        if (!Schema::hasTable('whatsapp_activity_logs')) {
            Schema::create('whatsapp_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->nullable()->index();
                $table->string('activity_type', 50)->index(); // request, response, failure, webhook
                $table->json('payload');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_activity_logs');
        Schema::dropIfExists('whatsapp_webhook_events');
        Schema::dropIfExists('message_campaigns');
        Schema::dropIfExists('message_audiences');
        Schema::dropIfExists('message_schedules');
        Schema::dropIfExists('event_message_mappings');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('tenant_whatsapp_accounts');
    }
};
