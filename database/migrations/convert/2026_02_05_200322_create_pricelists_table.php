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
        if (Schema::hasTable('pricelists')) {
            return;
        }
        Schema::create('pricelists', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code');
            $table->string('description', 1024);
            $table->uuid('currency_id')->index('idx_pricelists_currency_id_e92121a6');
            $table->boolean('is_master')->default(false);
            $table->boolean('active')->default(false);
            $table->string('document_no');
            $table->string('revision_number');
            $table->timestamps(6);
            $table->string('status', 100)->default('no-changes');
            $table->date('valid_till')->nullable();
            $table->string('pricelist_file')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricelists');
    }
};
