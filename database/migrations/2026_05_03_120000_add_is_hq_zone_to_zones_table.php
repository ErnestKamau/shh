<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table): void {
            if (!Schema::hasColumn('zones', 'is_hq_zone')) {
                $table->boolean('is_hq_zone')->default(false)->after('section_head_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table): void {
            if (Schema::hasColumn('zones', 'is_hq_zone')) {
                $table->dropColumn('is_hq_zone');
            }
        });
    }
};
