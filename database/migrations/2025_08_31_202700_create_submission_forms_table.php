<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_forms', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('naming_convention_prefix', 50)->default('SF');
            $table->string('naming_convention_format', 100)->default('{prefix}/{year}/{sequence}');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('version', 10)->default('1.0');
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');

            // Indexes for performance
            $table->index('name');
            $table->index(['is_published', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_forms');
    }
}