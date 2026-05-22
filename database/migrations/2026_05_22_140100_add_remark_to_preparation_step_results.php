<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preparation_step_results')) {
            return;
        }

        Schema::table('preparation_step_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('preparation_step_results', 'remark')) {
                $table->string('remark', 20)->nullable()->after('result');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('preparation_step_results')) {
            return;
        }

        Schema::table('preparation_step_results', function (Blueprint $table): void {
            if (Schema::hasColumn('preparation_step_results', 'remark')) {
                $table->dropColumn('remark');
            }
        });
    }
};
