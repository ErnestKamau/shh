<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $contactColumnRow = DB::selectOne('
            SELECT COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ', ['customerfeedbacks', 'contact_id']);
        $contactColumnType = $contactColumnRow->COLUMN_TYPE ?? null;

        $foreignKeyExists = collect(DB::select('
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ', ['customerfeedbacks', 'contact_id']))->isNotEmpty();

        Schema::table('customerfeedbacks', function (Blueprint $table) {
            if (!Schema::hasColumn('customerfeedbacks', 'contact_id')) {
                // crm_customer_contacts.id is a signed bigint in this project.
                $table->bigInteger('contact_id')->nullable()->after('customer_id');
            }
        });

        if (is_string($contactColumnType) && str_contains(strtolower($contactColumnType), 'unsigned')) {
            DB::statement('ALTER TABLE customerfeedbacks MODIFY contact_id BIGINT NULL');
        }

        if (!$foreignKeyExists) {
            Schema::table('customerfeedbacks', function (Blueprint $table) {
                $table->foreign('contact_id')
                    ->references('id')
                    ->on('crm_customer_contacts')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $foreignKeyExists = collect(DB::select('
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ', ['customerfeedbacks', 'contact_id']))->isNotEmpty();

            if ($foreignKeyExists) {
                $table->dropForeign(['contact_id']);
            }

            if (Schema::hasColumn('customerfeedbacks', 'contact_id')) {
                $table->dropColumn('contact_id');
            }
        });
    }
};
