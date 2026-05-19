<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE chain_of_custodies DROP COLUMN moved_in_by');
        DB::statement('ALTER TABLE chain_of_custodies ADD COLUMN moved_in_by uuid');
        
        DB::statement('ALTER TABLE chain_of_custodies DROP COLUMN moved_out_by');
        DB::statement('ALTER TABLE chain_of_custodies ADD COLUMN moved_out_by uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE chain_of_custodies DROP COLUMN moved_in_by');
        DB::statement('ALTER TABLE chain_of_custodies ADD COLUMN moved_in_by integer');
        
        DB::statement('ALTER TABLE chain_of_custodies DROP COLUMN moved_out_by');
        DB::statement('ALTER TABLE chain_of_custodies ADD COLUMN moved_out_by integer');
    }
};