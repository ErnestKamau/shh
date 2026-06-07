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
        Schema::create('sampling_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuid('crm_customer_id')->index();
            $table->uuid('contact_id')->nullable()->index();
            $table->dateTime('sampling_datetime');
            $table->string('location')->nullable();
            $table->uuid('sample_type_id')->nullable()->index();
            $table->uuid('analysis_type_id')->nullable()->index();
            $table->text('parameters')->nullable(); // Comma-separated or JSON list of analyte IDs/names
            $table->integer('number_of_samples')->default(1);
            $table->string('frequency'); // e.g. Daily, Weekly, One-time, etc.
            $table->boolean('notify_client')->default(false);
            $table->uuid('personnel_id')->index();
            $table->text('description')->nullable();
            $table->uuid('company_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampling_schedules');
    }
};
