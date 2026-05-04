<?php

use Illuminate\Database\Migrations\Migration;

// The sequence and column default were applied directly by the postgres superuser
// because gcla_app does not own the sample_type_categories table.
// This migration exists only to record the change in the migrations table.
return new class extends Migration
{
    public $connection = 'pgsql';

    public function up(): void
    {
        // Applied out-of-band by postgres:
        // CREATE SEQUENCE sample_type_categories_id_seq;
        // ALTER TABLE sample_type_categories ALTER COLUMN id SET DEFAULT nextval('sample_type_categories_id_seq');
        // ALTER SEQUENCE sample_type_categories_id_seq OWNED BY sample_type_categories.id;
        // GRANT USAGE ON SEQUENCE sample_type_categories_id_seq TO gcla_app;
    }

    public function down(): void
    {
        // To reverse: ALTER TABLE sample_type_categories ALTER COLUMN id DROP DEFAULT;
        // DROP SEQUENCE IF EXISTS sample_type_categories_id_seq;
    }
};

