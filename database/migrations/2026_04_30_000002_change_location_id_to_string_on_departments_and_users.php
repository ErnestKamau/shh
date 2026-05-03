<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        // inventory_departments.location_id: integer → varchar(255)
        DB::statement('ALTER TABLE inventory_departments ALTER COLUMN location_id TYPE varchar(255) USING NULL');

        // users.location_id: integer → varchar(255)
        DB::statement('ALTER TABLE users ALTER COLUMN location_id TYPE varchar(255) USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inventory_departments ALTER COLUMN location_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE users ALTER COLUMN location_id TYPE integer USING NULL');
    }
};
