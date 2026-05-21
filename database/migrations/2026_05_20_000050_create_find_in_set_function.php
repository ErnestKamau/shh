|"
|
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
        DB::unprepared("
            CREATE OR REPLACE FUNCTION find_in_set(str text, strlist text)
            RETURNS integer AS $$
            DECLARE
                pos integer;
            BEGIN
                pos := array_position(string_to_array(strlist, ','), str);
                RETURN COALESCE(pos, 0);
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS find_in_set(text, text);");
    }
};
