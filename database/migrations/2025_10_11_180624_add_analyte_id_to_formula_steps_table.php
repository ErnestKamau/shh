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
        Schema::table('formula_steps', function (Blueprint $table) {
            // Check if column already exists
            if (!Schema::hasColumn('formula_steps', 'analyte_id')) {
                $table->bigInteger('analyte_id')->nullable()->after('lookup_config');
            } else {
                // Column exists but might be wrong type, modify it
                DB::statement('ALTER TABLE formula_steps MODIFY analyte_id BIGINT NULL');
            }
            
            // Add foreign key if it doesn't exist
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'formula_steps' AND CONSTRAINT_NAME = 'formula_steps_analyte_id_foreign'");
            if (empty($foreignKeys)) {
                $table->foreign('analyte_id')->references('id')->on('analytes')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formula_steps', function (Blueprint $table) {
            if (Schema::hasColumn('formula_steps', 'analyte_id')) {
                $table->dropForeign(['analyte_id']);
                $table->dropColumn('analyte_id');
            }
        });
    }
};
