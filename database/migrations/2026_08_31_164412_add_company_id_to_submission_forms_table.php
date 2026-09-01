<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('submission_forms')) {
            return;
        }

        if (! Schema::hasColumn('submission_forms', 'company_id')) {
            Schema::table('submission_forms', function (Blueprint $table): void {
                $table->uuid('company_id')->nullable()->after('created_by')->index();

                if (Schema::hasTable('companies')) {
                    $table->foreign('company_id')
                        ->references('id')
                        ->on('companies')
                        ->nullOnDelete();
                }
            });
        }

        Schema::table('submission_forms', function (Blueprint $table): void {
            $table->dropUnique(['name']);
            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('submission_forms')) {
            return;
        }

        Schema::table('submission_forms', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'name']);
            $table->unique(['name']);
        });

        if (Schema::hasColumn('submission_forms', 'company_id')) {
            Schema::table('submission_forms', function (Blueprint $table): void {
                if (Schema::hasTable('companies')) {
                    $table->dropForeign(['company_id']);
                }

                $table->dropColumn('company_id');
            });
        }
    }
};
