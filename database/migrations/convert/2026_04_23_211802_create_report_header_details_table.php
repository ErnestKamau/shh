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
        Schema::create('report_header_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->string('to');
            $table->string('cc');
            $table->date('date');
            $table->string('ref');
            $table->string('re');
            $table->string('for');
            $table->uuid('sample_header_id')->index('idx_report_header_details_sample_header_id_582530a4');
            $table->integer('specific_analyst_id');
            $table->integer('approved_by_id');
            $table->integer('verified_by_id');
            $table->string('model');
            $table->integer('model_id');
            $table->timestamps();
            $table->string('from')->nullable();
            $table->string('outgoing_email_body', 2048)->nullable();
            $table->foreign(['sample_header_id'], 'fk_report_header_details_sample_header_id_2552a1da')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_header_details');
    }
};
