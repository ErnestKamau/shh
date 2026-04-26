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
        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('supplier_contacts_supplier_id_foreign');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pin')->nullable();
            $table->string('id_number')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_supplier_contacts_supplier_id_080e6a73')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_contacts');
    }
};
