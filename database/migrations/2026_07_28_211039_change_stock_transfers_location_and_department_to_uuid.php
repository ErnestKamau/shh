<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE stock_transfers ALTER COLUMN location_id TYPE uuid USING NULL');
        DB::statement('ALTER TABLE stock_transfers ALTER COLUMN department_id TYPE uuid USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock_transfers ALTER COLUMN location_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE stock_transfers ALTER COLUMN department_id TYPE integer USING NULL');
    }
};
