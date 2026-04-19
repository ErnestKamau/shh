<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUncertaintySourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uncertainty_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uncertainty_budget_id');
            $table->string('source_name');
            $table->enum('type', ['A', 'B']);
            $table->decimal('std_uncertainty_value', 10, 6);
            $table->decimal('sensitivity_coefficient', 10, 6)->default(1.000000);
            $table->decimal('contribution_value', 10, 6)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Note: Foreign key constraints removed for MariaDB compatibility

            // Indexes
            $table->index('uncertainty_budget_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uncertainty_sources');
    }
}