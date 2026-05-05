<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // model_id is varchar but users.id is uuid — PostgreSQL refuses the comparison.
        // All existing values are valid UUIDs (verified before this migration).
        DB::statement('ALTER TABLE spatie_model_has_roles ALTER COLUMN model_id TYPE uuid USING model_id::uuid');
        DB::statement('ALTER TABLE spatie_model_has_permissions ALTER COLUMN model_id TYPE uuid USING model_id::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE spatie_model_has_roles ALTER COLUMN model_id TYPE varchar USING model_id::varchar');
        DB::statement('ALTER TABLE spatie_model_has_permissions ALTER COLUMN model_id TYPE varchar USING model_id::varchar');
    }
};
