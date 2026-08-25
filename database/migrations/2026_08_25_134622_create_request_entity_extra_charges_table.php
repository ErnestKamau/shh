<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('request_entity_extra_charges')) {
            return;
        }

        Schema::create('request_entity_extra_charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('request_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('currency_id')->nullable();
            $table->decimal('cost', 18, 2)->nullable()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_entity_extra_charges');
    }
};
