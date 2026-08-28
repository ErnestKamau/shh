<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('standards_analytes')) {
            return;
        }

        Schema::table('standards_analytes', function (Blueprint $table): void {
            if (! Schema::hasColumn('standards_analytes', 'pass_comment')) {
                $table->text('pass_comment')->nullable()->after('comments');
            }
            if (! Schema::hasColumn('standards_analytes', 'fail_comment')) {
                $table->text('fail_comment')->nullable()->after('pass_comment');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('standards_analytes')) {
            return;
        }

        Schema::table('standards_analytes', function (Blueprint $table): void {
            foreach (['pass_comment', 'fail_comment'] as $column) {
                if (Schema::hasColumn('standards_analytes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
