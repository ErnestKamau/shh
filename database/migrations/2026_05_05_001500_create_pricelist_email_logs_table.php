<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricelist_email_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('pricelist_id')->constrained('pricelists')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('crm_customers')->nullOnDelete();
            $table->foreignUuid('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('recipient_email');
            $table->string('subject');
            $table->boolean('has_attachment')->default(false);
            $table->string('attachment_name')->nullable();
            $table->string('status', 30)->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['pricelist_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricelist_email_logs');
    }
};
