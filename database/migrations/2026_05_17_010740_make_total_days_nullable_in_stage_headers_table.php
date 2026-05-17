<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stage_headers', function (Blueprint $table) {
            $table->integer('total_days')->nullable()->default(0)->change();
        });

        DB::table('stage_headers')->whereNull('total_days')->update(['total_days' => 0]);
    }

    public function down(): void
    {
        DB::table('stage_headers')->whereNull('total_days')->update(['total_days' => 0]);

        Schema::table('stage_headers', function (Blueprint $table) {
            $table->integer('total_days')->nullable(false)->default(null)->change();
        });
    }
};
