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
        Schema::create('naming_convension_consensuses', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('string_part');
            $table->string('integer_part');
            $table->string('model');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_naming_convension_consensuses_company_id_7e52b2e7');
            $table->foreign(['company_id'], 'fk_naming_convension_consensuses_company_id_bfef9c35')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('naming_convension_consensuses');
    }
};
