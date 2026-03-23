<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            if (! Schema::hasColumn('procedure_worksheet_steps', 'value_type')) {
                $table->string('value_type', 20)->default('text');
            }

            if (! Schema::hasColumn('procedure_worksheet_steps', 'default_value')) {
                $table->text('default_value')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            if (Schema::hasColumn('procedure_worksheet_steps', 'value_type')) {
                $table->dropColumn('value_type');
            }

            if (Schema::hasColumn('procedure_worksheet_steps', 'default_value')) {
                $table->dropColumn('default_value');
            }
        });
    }
};

