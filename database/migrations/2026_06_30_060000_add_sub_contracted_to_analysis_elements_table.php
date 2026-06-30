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
        if (! Schema::hasTable('analysis_elements') || Schema::hasColumn('analysis_elements', 'sub_contracted')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->boolean('sub_contracted')->default(false)->after('non_accredited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('analysis_elements') || ! Schema::hasColumn('analysis_elements', 'sub_contracted')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->dropColumn('sub_contracted');
        });
    }
};
