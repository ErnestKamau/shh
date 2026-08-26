<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('o_t_p_s')) {
            return;
        }

        // request_entities.id is uuid; OTP.model_id was still integer.
        DB::statement('ALTER TABLE o_t_p_s ALTER COLUMN model_id TYPE varchar USING model_id::varchar');

        if (Schema::hasColumn('o_t_p_s', 'approved_by')) {
            DB::statement('ALTER TABLE o_t_p_s ALTER COLUMN approved_by TYPE varchar USING approved_by::varchar');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('o_t_p_s')) {
            return;
        }

        if (Schema::hasColumn('o_t_p_s', 'approved_by')) {
            DB::statement('ALTER TABLE o_t_p_s ALTER COLUMN approved_by TYPE integer USING NULLIF(approved_by, \'\')::integer');
        }

        DB::statement('ALTER TABLE o_t_p_s ALTER COLUMN model_id TYPE integer USING NULLIF(model_id, \'\')::integer');
    }
};
