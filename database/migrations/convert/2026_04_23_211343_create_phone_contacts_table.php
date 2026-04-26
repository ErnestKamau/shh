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
        Schema::create('phone_contacts', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('company_id')->index('idx_phone_contacts_company_id_ec32f7e3');
            $table->integer('entity_id');
            $table->string('entity_type');
            $table->timestamps();
            $table->foreign(['company_id'], 'fk_phone_contacts_company_id_cbd9c3f3')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_contacts');
    }
};
