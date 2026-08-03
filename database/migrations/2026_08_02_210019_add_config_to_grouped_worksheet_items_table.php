<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grouped_worksheet_items', function (Blueprint $table) {
            $table->jsonb('config')->nullable()->after('is_required');
        });
    }

    public function down(): void
    {
        Schema::table('grouped_worksheet_items', function (Blueprint $table) {
            $table->dropColumn('config');
        });
    }
};
