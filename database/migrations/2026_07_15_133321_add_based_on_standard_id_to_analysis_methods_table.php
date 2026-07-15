<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('analysis_methods', 'based_on_standard_id')) {
            Schema::table('analysis_methods', function (Blueprint $table) {
                $table->uuid('based_on_standard_id')->nullable()->after('sample_header_id');
                $table->index('based_on_standard_id');
                $table->foreign('based_on_standard_id')
                    ->references('id')
                    ->on('standards')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('analysis_methods', 'based_on_standard_id')) {
            Schema::table('analysis_methods', function (Blueprint $table) {
                $table->dropForeign(['based_on_standard_id']);
                $table->dropIndex(['based_on_standard_id']);
                $table->dropColumn('based_on_standard_id');
            });
        }
    }
};
