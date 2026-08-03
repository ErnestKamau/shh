<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheets', function (Blueprint $table) {
            $table->jsonb('layout_settings')->nullable()->after('config_fields_placement');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheets', function (Blueprint $table) {
            $table->dropColumn('layout_settings');
        });
    }
};
