<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_sections', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_id')->references('id')->on('submission_forms')->onDelete('cascade');

            // Indexes for performance
            $table->index('submission_form_id');
            $table->index(['submission_form_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_sections');
    }
}