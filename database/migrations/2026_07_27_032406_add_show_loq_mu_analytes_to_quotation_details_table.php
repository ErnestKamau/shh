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
            if (! Schema::hasColumn('quotation_details', 'show_loq_analytes')) {
                $table->text('show_loq_analytes')->nullable()->after('mu_percent');
            }
            if (! Schema::hasColumn('quotation_details', 'show_mu_analytes')) {
                $table->text('show_mu_analytes')->nullable()->after('show_loq_analytes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        Schema::table('quotation_details', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('quotation_details', 'show_loq_analytes') ? 'show_loq_analytes' : null,
                Schema::hasColumn('quotation_details', 'show_mu_analytes') ? 'show_mu_analytes' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
