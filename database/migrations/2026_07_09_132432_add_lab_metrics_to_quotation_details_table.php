<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        Schema::table('quotation_details', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotation_details', 'test_method')) {
                $table->string('test_method')->nullable()->after('description');
            }
            if (! Schema::hasColumn('quotation_details', 'loq')) {
                $table->string('loq')->nullable()->after('test_method');
            }
            if (! Schema::hasColumn('quotation_details', 'mu_percent')) {
                $table->string('mu_percent')->nullable()->after('loq');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        Schema::table('quotation_details', function (Blueprint $table): void {
            $columns = ['test_method', 'loq', 'mu_percent'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('quotation_details', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
