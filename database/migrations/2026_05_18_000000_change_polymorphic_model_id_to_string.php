<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Polymorphic model_id columns in entity_approvals, entity_attachments, and entity_notes
        // were originally integer, which breaks support for UUID keys like request_entities.id.
        // We alter them to varchar to store both integer and UUID keys seamlessly in PostgreSQL.
        DB::statement('ALTER TABLE entity_approvals ALTER COLUMN model_id TYPE varchar USING model_id::varchar');
        DB::statement('ALTER TABLE entity_attachments ALTER COLUMN model_id TYPE varchar USING model_id::varchar');
        DB::statement('ALTER TABLE entity_notes ALTER COLUMN model_id TYPE varchar USING model_id::varchar');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE entity_approvals ALTER COLUMN model_id TYPE integer USING model_id::integer');
        DB::statement('ALTER TABLE entity_attachments ALTER COLUMN model_id TYPE integer USING model_id::integer');
        DB::statement('ALTER TABLE entity_notes ALTER COLUMN model_id TYPE integer USING model_id::integer');
    }
};
