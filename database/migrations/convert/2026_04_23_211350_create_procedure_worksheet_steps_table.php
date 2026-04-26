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
        Schema::create('procedure_worksheet_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('procedure_worksheet_steps_procedure_worksheet_id_foreign');
            $table->string('step');
            $table->unsignedInteger('order')->nullable()->default(0);
            $table->boolean('is_active')->default(true);
            $table->longText('default_equipment_id')->nullable();
            $table->longText('default_analyst_id')->nullable();
            $table->longText('default_measurand_ids')->nullable();
            $table->timestamps();
            $table->string('value_type', 20)->default('text');
            $table->text('default_value')->nullable();
            $table->longText('default_measurand_values')->nullable();
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_worksheet_steps_procedure_worksheet_id_23f70259')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_worksheet_steps');
    }
};
