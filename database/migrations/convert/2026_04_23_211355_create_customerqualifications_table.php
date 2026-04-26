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
        Schema::create('customerqualifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('qualification_id')->nullable()->index('idx_customerqualifications_qualification_id_13695b99');
            $table->integer('customer_id');
            $table->dateTime('certification_date');
            $table->dateTime('expire_date');
            $table->string('certification_body');
            $table->string('certificate');
            $table->string('edited')->nullable();
            $table->boolean('status')->default(false);
            $table->string('name', 500)->nullable();
            $table->foreign(['qualification_id'], 'fk_customerqualifications_qualification_id_89f70621')->references(['id'])->on('qualifications')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customerqualifications');
    }
};
