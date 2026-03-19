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
        Schema::table('captured_results', function (Blueprint $table) {
            if (! Schema::hasColumn('captured_results', 'worksheet_posted')) {
                $table->boolean('worksheet_posted')
                    ->default(false)
                    ->after('procedure_worksheet_id')
                    ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_results', function (Blueprint $table) {
            if (Schema::hasColumn('captured_results', 'worksheet_posted')) {
                $table->dropColumn('worksheet_posted');
            }
        });
    }
};

