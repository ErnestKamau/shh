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
        Schema::create('ser_step_worksheet_sample_relations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ser_header_id')->index('idx_ser_step_worksheet_sample_relations_ser_header_id_dd375989');
            $table->uuid('ser_worksheet_step_id')->nullable()->index('idx_ser_step_worksheet_sample_relations_ser_worksheet_52469537');
            $table->integer('step_number')->default(0);
            $table->unsignedInteger('measurand_id')->nullable();
            $table->uuid('equipment_id')->nullable()->index('idx_ser_step_worksheet_sample_relations_equipment_id_f4ea3ca2');
            $table->unsignedBigInteger('analyst_id')->nullable();
            $table->string('result_value')->nullable();
            $table->timestamps();
            $table->foreign(['ser_header_id'], 'fk_stp_hdr_id')->references(['id'])->on('ser_header_worksheet_sample_relations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_ser_step_worksheet_sample_relations_equipment_id_7babb0d0')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ser_worksheet_step_id'], 'fk_ser_step_worksheet_sample_relations_ser_worksheet_s_5e3b6ff7')->references(['id'])->on('ser_worksheet_steps')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_step_worksheet_sample_relations');
    }
};
