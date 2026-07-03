<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('iso_audits') || Schema::hasColumn('iso_audits', 'department')) {
            return;
        }

        Schema::table('iso_audits', function (Blueprint $table) {
            $table->string('department')->nullable()->after('criteria');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('iso_audits') || ! Schema::hasColumn('iso_audits', 'department')) {
            return;
        }

        Schema::table('iso_audits', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
