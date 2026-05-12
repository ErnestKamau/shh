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
        // Drop the auto-increment bigint id and replace with char(36) for UUID storage
        DB::statement('ALTER TABLE audits DROP PRIMARY KEY, MODIFY id CHAR(36) NOT NULL, ADD PRIMARY KEY (id)');

        // Also fix user_id to char(36) to match uuid users.id
        if (Schema::hasColumn('audits', 'user_id')) {
            DB::statement('ALTER TABLE audits MODIFY user_id CHAR(36) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE audits DROP PRIMARY KEY, MODIFY id BIGINT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
        DB::statement('ALTER TABLE audits MODIFY user_id BIGINT NULL');
    }
};
