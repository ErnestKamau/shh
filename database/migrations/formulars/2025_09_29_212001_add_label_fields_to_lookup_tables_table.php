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
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->string('key_label')->nullable()->after('key_columns');
            $table->string('value_label')->nullable()->after('value_column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->dropColumn(['key_label', 'value_label']);
        });
    }
};
