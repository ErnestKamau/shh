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
            $table->text('name');
            $table->string('logo')->default('/images/no-logo.png');
            $table->text('location')->nullable();
            $table->text('address');
            $table->uuid('country_id')->index('idx_companies_country_id_dbeffba5');
            $table->text('website');
            $table->timestamps();
            $table->text('license_key')->nullable();
            $table->text('license_expiry')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('show_on_reports')->default(false);
            $table->text('client_number')->nullable();
            $table->text('email')->nullable();
            $table->text('cell_phone')->nullable();
            $table->string('telephone')->default('');
            $table->text('street')->nullable();
            $table->text('fax')->nullable();
            $table->text('report_logo')->nullable();

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
