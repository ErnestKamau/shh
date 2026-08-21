<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_approvals', function (Blueprint $table) {
            if (! Schema::hasColumn('entity_approvals', 'link_key')) {
                $table->string('link_key')->nullable()->after('description');
            }

            if (! Schema::hasColumn('entity_approvals', 'is_current')) {
                $table->boolean('is_current')->default(false)->nullable()->after('link_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entity_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('entity_approvals', 'is_current')) {
                $table->dropColumn('is_current');
            }

            if (Schema::hasColumn('entity_approvals', 'link_key')) {
                $table->dropColumn('link_key');
            }
        });
    }
};
