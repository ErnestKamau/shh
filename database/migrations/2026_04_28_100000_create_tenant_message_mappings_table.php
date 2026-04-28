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
        if (!Schema::hasTable('tenant_message_mappings')) {
            Schema::create('tenant_message_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id', 100);
                $table->string('event_code', 100);
                $table->string('provider_template_id');
                $table->string('provider_template_name')->nullable();
                $table->string('language', 20)->default('en');
                $table->string('wa_number', 32)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->unique(['tenant_id', 'event_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_message_mappings');
    }
};