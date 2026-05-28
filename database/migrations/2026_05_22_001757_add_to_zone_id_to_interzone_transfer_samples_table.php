<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interzone_transfer_samples', function (Blueprint $table) {
            $table->uuid('to_zone_id')->nullable()->after('sample_detail_id')->index();

            $table->foreign('to_zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();
        });

        Schema::table('interzone_transfers', function (Blueprint $table) {
            $table->uuid('to_zone_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('interzone_transfer_samples', function (Blueprint $table) {
            $table->dropForeign(['to_zone_id']);
            $table->dropColumn('to_zone_id');
        });

        Schema::table('interzone_transfers', function (Blueprint $table) {
            $table->uuid('to_zone_id')->nullable(false)->change();
        });
    }
};
