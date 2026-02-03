<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we can use DB::statement to modify the enum
        DB::statement("ALTER TABLE method_sequence_runs MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE method_sequence_runs MODIFY COLUMN status ENUM('in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'in_progress'");
    }
};
