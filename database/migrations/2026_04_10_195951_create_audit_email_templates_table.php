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
        Schema::create('audit_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_code');
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->json('variables')->nullable();
            $table->unsignedBigInteger('notification_type_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'template_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_email_templates');
    }
};
