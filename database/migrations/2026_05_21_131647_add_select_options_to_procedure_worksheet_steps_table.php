<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->json('select_options')->nullable()->after('default_measurand_values');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->dropColumn('select_options');
        });
    }
};
