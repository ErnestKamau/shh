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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('logo')->default('/images/no-logo.png');
            $table->string('location')->nullable();
            $table->string('address');
            $table->uuid('country_id')->index('idx_companies_country_id_dbeffba5');
            $table->string('website');
            $table->timestamps();
            $table->string('license_key', 512)->nullable();
            $table->date('license_expiry')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('show_on_reports')->default(false);
            $table->string('client_number', 100)->nullable();
            $table->string('email')->default('');
            $table->string('cell_phone')->default('');
            $table->string('telephone')->default('');
            $table->string('street')->default('');
            $table->string('fax', 100)->nullable();
            $table->text('report_logo')->nullable();
            $table->foreign(['country_id'], 'fk_companies_country_id_34dce04f')->references(['id'])->on('countries')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
