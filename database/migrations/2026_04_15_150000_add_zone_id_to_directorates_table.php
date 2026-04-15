<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddZoneIdToDirectoratesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('directorates', 'zone_id')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->unsignedBigInteger('zone_id')->nullable()->index()->after('head_id');
            });
        }

        if (!$this->foreignKeyExists('directorates', 'directorates_zone_id_foreign')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->foreign('zone_id')
                    ->references('id')
                    ->on('zones')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if ($this->foreignKeyExists('directorates', 'directorates_zone_id_foreign')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->dropForeign('directorates_zone_id_foreign');
            });
        }

        if (Schema::hasColumn('directorates', 'zone_id')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->dropColumn('zone_id');
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
}
