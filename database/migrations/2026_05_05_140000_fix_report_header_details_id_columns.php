<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // model_id is polymorphic — stores UUID PKs from SampleHeader and CRMCustomer
        // specific_analyst_id, approved_by_id, verified_by_id reference users.id (uuid)
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN model_id TYPE varchar USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN model_id DROP NOT NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN specific_analyst_id TYPE uuid USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN specific_analyst_id DROP NOT NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN approved_by_id TYPE uuid USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN approved_by_id DROP NOT NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN verified_by_id TYPE uuid USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN verified_by_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN model_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN specific_analyst_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN approved_by_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE report_header_details ALTER COLUMN verified_by_id TYPE integer USING NULL');
    }
};
