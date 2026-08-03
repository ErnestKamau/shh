<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grouped_worksheet_holders', function (Blueprint $table) {
            $table->jsonb('settings')->nullable()->after('issue_date');
        });
    }

    public function down(): void
    {
        Schema::table('grouped_worksheet_holders', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
