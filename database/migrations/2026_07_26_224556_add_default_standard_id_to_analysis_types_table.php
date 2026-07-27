<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analysis_types')) {
            return;
        }

        if (! Schema::hasColumn('analysis_types', 'default_standard_id')) {
            Schema::table('analysis_types', function (Blueprint $table) {
                $table->uuid('default_standard_id')->nullable();
                $table->index('default_standard_id');
                $table->foreign('default_standard_id')
                    ->references('id')
                    ->on('standards')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('analysis_types')) {
            return;
        }

        if (Schema::hasColumn('analysis_types', 'default_standard_id')) {
            Schema::table('analysis_types', function (Blueprint $table) {
                $table->dropForeign(['default_standard_id']);
                $table->dropIndex(['default_standard_id']);
                $table->dropColumn('default_standard_id');
            });
        }
    }
};
