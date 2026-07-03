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
        if (Schema::hasTable('lookup_table_entries')) {
            return;
        }
        Schema::create('lookup_table_entries', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('lookup_table_id')->index('idx_lookup_table_entries_lookup_table_id_081ee37e');
            $table->longText('keys');
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['lookup_table_id', 'deleted_at']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookup_table_entries');
    }
};
