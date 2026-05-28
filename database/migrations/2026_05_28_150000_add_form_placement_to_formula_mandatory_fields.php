<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('formula_mandatory_fields') && ! Schema::hasColumn('formula_mandatory_fields', 'form_placement')) {
            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->string('form_placement', 16)->default('bottom')->after('order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('formula_mandatory_fields') && Schema::hasColumn('formula_mandatory_fields', 'form_placement')) {
            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->dropColumn('form_placement');
            });
        }
    }
};
