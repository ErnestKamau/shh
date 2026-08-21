<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align supplier_r_f_q_s.request_id with UUID request entities.
     */
    public function up(): void
    {
        if (! Schema::hasTable('supplier_r_f_q_s')) {
            return;
        }

        DB::statement('ALTER TABLE supplier_r_f_q_s ALTER COLUMN request_id DROP DEFAULT');
        DB::statement('ALTER TABLE supplier_r_f_q_s ALTER COLUMN request_id TYPE uuid USING NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('supplier_r_f_q_s')) {
            return;
        }

        DB::statement('ALTER TABLE supplier_r_f_q_s ALTER COLUMN request_id TYPE integer USING NULL');
    }
};
