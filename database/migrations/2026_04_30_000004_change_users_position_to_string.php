<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN position TYPE varchar(255) USING CASE WHEN position IS NULL THEN NULL ELSE position::text END');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users ALTER COLUMN position TYPE integer USING CASE WHEN position IS NULL OR position = '' THEN NULL ELSE NULLIF(position, '')::integer END");
    }
};