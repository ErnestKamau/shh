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
        if (!Schema::hasTable('outbound_messages')) {
            Schema::create('outbound_messages', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100)->index();
                $table->string('event_code', 100)->index();
                $table->string('recipient', 32);
                $table->string('provider_template_id');
                $table->json('payload_json');
                $table->string('status', 30)->default('queued')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->string('provider_message_id')->nullable()->index();
                $table->json('provider_response_json')->nullable();
                $table->text('error')->nullable();
                $table->string('idempotency_key');
                $table->timestamps();

                $table->unique(['tenant_id', 'idempotency_key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_messages');
    }
};