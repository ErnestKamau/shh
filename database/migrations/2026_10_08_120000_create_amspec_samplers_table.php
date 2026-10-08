<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recurring AmSpec personnel who sample TRFs, for the "Submit & sign" typeahead
     * (Name + Employee ID autofill when "Sampled by" is the lab itself).
     */
    public function up(): void
    {
        Schema::create('amspec_samplers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_id', 32)->nullable();
            $table->unsignedInteger('use_count')->default(1);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['name', 'employee_id'], 'amspec_samplers_name_employee_id_unique');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amspec_samplers');
    }
};
