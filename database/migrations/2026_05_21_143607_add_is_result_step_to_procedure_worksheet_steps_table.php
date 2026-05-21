<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->boolean('is_result_step')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->dropColumn('is_result_step');
        });
    }
};
