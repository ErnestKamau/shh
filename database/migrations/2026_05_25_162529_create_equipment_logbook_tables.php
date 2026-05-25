<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_logbook_columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id')->index();
            $table->string('label');
            $table->string('key');
            $table->enum('column_type', ['input', 'derived', 'dataset'])->default('input');
            $table->enum('input_data_type', ['string', 'number', 'date', 'boolean', 'textarea'])->default('string');
            $table->text('expression')->nullable();
            $table->json('dataset_config')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->timestamps();
            $table->unique(['equipment_id', 'key'], 'equipment_logbook_column_key_unique');
        });

        Schema::create('equipment_logbook_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id')->index();
            $table->timestamp('logged_at');
            $table->unsignedBigInteger('logged_by')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['equipment_id', 'logged_at']);
        });

        Schema::create('equipment_logbook_entry_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entry_id')->index();
            $table->uuid('column_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['entry_id', 'column_id'], 'equipment_logbook_entry_value_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_logbook_entry_values');
        Schema::dropIfExists('equipment_logbook_entries');
        Schema::dropIfExists('equipment_logbook_columns');
    }
};
