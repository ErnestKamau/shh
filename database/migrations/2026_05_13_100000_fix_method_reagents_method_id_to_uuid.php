<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixMethodReagentsMethodIdToUuid extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to convert method_id from integer to uuid
        DB::statement('ALTER TABLE method_reagents ALTER COLUMN method_id TYPE uuid USING method_id::text::uuid');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert method_id from uuid to integer
        DB::statement('ALTER TABLE method_reagents ALTER COLUMN method_id TYPE integer');
    }
}
