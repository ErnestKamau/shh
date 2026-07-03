<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batch_comments') || ! Schema::hasColumn('batch_comments', 'reminder_for')) {
            return;
        }

        DB::statement('ALTER TABLE batch_comments ALTER COLUMN reminder_for DROP NOT NULL');
        DB::statement('ALTER TABLE batch_comments ALTER COLUMN reminder_for TYPE uuid USING NULL::uuid');
    }

    public function down(): void
    {
        if (! Schema::hasTable('batch_comments') || ! Schema::hasColumn('batch_comments', 'reminder_for')) {
            return;
        }

        DB::statement('ALTER TABLE batch_comments ALTER COLUMN reminder_for TYPE integer USING NULL::integer');
        DB::statement('ALTER TABLE batch_comments ALTER COLUMN reminder_for SET NOT NULL');
    }
};
