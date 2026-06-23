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
        Schema::create('test_request_report_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id');
            $table->unsignedSmallInteger('revision_no')->default(1);
            $table->string('channel');               // email | whatsapp | portal
            $table->string('recipient_name')->nullable();
            $table->string('recipient_contact')->nullable(); // email or phone
            $table->string('status')->default('sent'); // sent | failed
            $table->text('error')->nullable();
            $table->uuid('sent_by')->nullable();
            $table->timestamps();

            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_request_report_deliveries');
    }
};
