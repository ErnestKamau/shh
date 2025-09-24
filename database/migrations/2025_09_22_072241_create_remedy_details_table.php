<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemedyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remedy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remedy_header_id')->constrained()->onDelete('cascade');
            $table->string('antibiotic');
            $table->enum('sensitivity', ['Sensitive', 'Resistant', 'Intermediate']);
            $table->string('dimension')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('remedy_details');
    }
}