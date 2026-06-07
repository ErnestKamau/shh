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
        Schema::create('customer_whatsapp_preferences', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('customer_id'); // Match CustomerContact or CRM customer identifier
            $blueprint->string('tenant_id'); // Scope by laboratory/tenant
            $blueprint->boolean('opted_in')->default(true);
            $blueprint->timestamp('opted_in_at')->nullable();
            $blueprint->timestamp('opted_out_at')->nullable();
            $blueprint->string('source')->nullable(); // e.g. web, sms, admin, signup
            $blueprint->timestamps();

            // Indexing for performance
            $blueprint->index(['tenant_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_whatsapp_preferences');
    }
};
