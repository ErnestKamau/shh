<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            if (! Schema::hasColumn('procedure_worksheet_steps', 'default_measurand_values')) {
                $table->json('default_measurand_values')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            if (Schema::hasColumn('procedure_worksheet_steps', 'default_measurand_values')) {
                $table->dropColumn('default_measurand_values');
            }
        });
    }
};

