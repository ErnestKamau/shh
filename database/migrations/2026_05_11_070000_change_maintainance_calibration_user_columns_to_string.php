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
        DB::statement('ALTER TABLE maintainance_calibration_logs ALTER COLUMN overseen_by TYPE varchar(255) USING overseen_by::text');
        DB::statement('ALTER TABLE maintainance_calibration_logs ALTER COLUMN edit_by TYPE varchar(255) USING edit_by::text');
        DB::statement('ALTER TABLE maintainance_calibration_logs ALTER COLUMN employee_id TYPE varchar(255) USING employee_id::text');
        DB::statement('ALTER TABLE maintainance_calibration_logs ALTER COLUMN operator_id TYPE varchar(255) USING operator_id::text');
        DB::statement('ALTER TABLE maintainance_calibration_logs ALTER COLUMN proccess_owner_id TYPE varchar(255) USING proccess_owner_id::text');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE maintainance_calibration_logs ALTER COLUMN overseen_by TYPE integer USING (CASE WHEN overseen_by ~ '^[0-9]+$' THEN overseen_by::integer ELSE NULL END)");
        DB::statement("ALTER TABLE maintainance_calibration_logs ALTER COLUMN edit_by TYPE integer USING (CASE WHEN edit_by ~ '^[0-9]+$' THEN edit_by::integer ELSE NULL END)");
        DB::statement("ALTER TABLE maintainance_calibration_logs ALTER COLUMN employee_id TYPE integer USING (CASE WHEN employee_id ~ '^[0-9]+$' THEN employee_id::integer ELSE NULL END)");
        DB::statement("ALTER TABLE maintainance_calibration_logs ALTER COLUMN operator_id TYPE integer USING (CASE WHEN operator_id ~ '^[0-9]+$' THEN operator_id::integer ELSE NULL END)");
        DB::statement("ALTER TABLE maintainance_calibration_logs ALTER COLUMN proccess_owner_id TYPE integer USING (CASE WHEN proccess_owner_id ~ '^[0-9]+$' THEN proccess_owner_id::integer ELSE NULL END)");
    }
};
