<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDirectoratesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('directorates')) {
            Schema::create('directorates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique();
                $table->bigInteger('head_id')->nullable()->index();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if ($this->columnType('directorates', 'head_id') === 'bigint unsigned') {
            DB::statement('ALTER TABLE directorates MODIFY head_id BIGINT NULL');
        }

        if (!$this->foreignKeyExists('directorates', 'directorates_head_id_foreign')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->foreign('head_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->foreignKeyExists('directorates', 'directorates_head_id_foreign')) {
            Schema::table('directorates', function (Blueprint $table) {
                $table->dropForeign('directorates_head_id_foreign');
            });
        }

        Schema::dropIfExists('directorates');
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

    private function columnType(string $table, string $column): ?string
    {
        return DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->value('COLUMN_TYPE');
    }
}
