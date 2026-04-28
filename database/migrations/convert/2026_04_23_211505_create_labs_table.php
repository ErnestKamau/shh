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
        Schema::create('labs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_labs_company_id_c8940f3e');
            $table->uuid('directorate_id')->nullable()->index('idx_labs_directorate_id_f255f3bb');
            $table->uuid('zone_id')->nullable()->index('idx_labs_zone_id_f3373fca');
            $table->uuid('manager_id')->nullable()->index('idx_labs_manager_id_665f9872');
            $table->json('analyst_ids')->nullable();
            $table->boolean('is_external')->default(false);
            $table->string('phone1');
            $table->string('phone2')->nullable();
            $table->string('phone3')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->string('start_sample_no', 100)->nullable();

            $table->unique(['directorate_id', 'code'], 'labs_directorate_code_unique');
            $table->unique(['directorate_id', 'name'], 'labs_directorate_name_unique');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
