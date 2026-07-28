<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE currency_conversions ALTER COLUMN currency_1 TYPE uuid USING NULL');
        DB::statement('ALTER TABLE currency_conversions ALTER COLUMN currency_2 TYPE uuid USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE currency_conversions ALTER COLUMN currency_1 TYPE integer USING NULL');
        DB::statement('ALTER TABLE currency_conversions ALTER COLUMN currency_2 TYPE integer USING NULL');
    }
};
