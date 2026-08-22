<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pricelists')) {
            return;
        }

        Schema::table('pricelists', function (Blueprint $table): void {
            if (! Schema::hasColumn('pricelists', 'billing_mode')) {
                $table->string('billing_mode', 20)->default('package')->after('is_master');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pricelists')) {
            return;
        }

        Schema::table('pricelists', function (Blueprint $table): void {
            if (Schema::hasColumn('pricelists', 'billing_mode')) {
                $table->dropColumn('billing_mode');
            }
        });
    }
};
