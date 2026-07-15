<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sample_analysis_stages') || ! Schema::hasColumn('sample_analysis_stages', 'section_head_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Legacy column was integer; users.id is uuid. Clear incompatible values then alter type.
            DB::statement('ALTER TABLE sample_analysis_stages ALTER COLUMN section_head_id DROP DEFAULT');
            DB::statement('UPDATE sample_analysis_stages SET section_head_id = NULL');
            DB::statement('ALTER TABLE sample_analysis_stages ALTER COLUMN section_head_id TYPE uuid USING NULL');
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('sample_analysis_stages', function (Blueprint $table) {
                $table->dropColumn('section_head_id');
            });
            Schema::table('sample_analysis_stages', function (Blueprint $table) {
                $table->uuid('section_head_id')->nullable()->after('level');
            });

            return;
        }

        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            $table->uuid('section_head_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sample_analysis_stages') || ! Schema::hasColumn('sample_analysis_stages', 'section_head_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('UPDATE sample_analysis_stages SET section_head_id = NULL');
            DB::statement('ALTER TABLE sample_analysis_stages ALTER COLUMN section_head_id TYPE integer USING NULL');
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('sample_analysis_stages', function (Blueprint $table) {
                $table->dropColumn('section_head_id');
            });
            Schema::table('sample_analysis_stages', function (Blueprint $table) {
                $table->integer('section_head_id')->nullable();
            });

            return;
        }

        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            $table->integer('section_head_id')->nullable()->change();
        });
    }
};
