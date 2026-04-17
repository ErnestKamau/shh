<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('raw_results') || Schema::hasColumn('raw_results', 'is_readability')) {
            return;
        }

        Schema::table('raw_results', function (Blueprint $table) {
            $table->boolean('is_readability')->default(0)->after('is_accepted');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('raw_results') || !Schema::hasColumn('raw_results', 'is_readability')) {
            return;
        }

        Schema::table('raw_results', function (Blueprint $table) {
            $table->dropColumn('is_readability');
        });
    }
};
