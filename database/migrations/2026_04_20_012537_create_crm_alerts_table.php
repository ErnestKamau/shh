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
        Schema::create('crm_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('system'); // system, health, custom
            $table->string('level')->default('info'); // info, warning, danger, success
            $table->string('icon')->nullable();
            $table->text('message');
            $table->unsignedBigInteger('user_id')->nullable(); // Target user or null for global
            $table->nullableMorphs('relatable'); // Allows linking to CRMCustomer, Complaint, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_alerts');
    }
};
