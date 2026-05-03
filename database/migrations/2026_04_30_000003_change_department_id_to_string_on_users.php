<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN department_id TYPE varchar(255) USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN department_id TYPE integer USING NULL');
    }
};
