<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Change model_id from bigint to varchar(255) in the Spatie pivot tables so
 * that UUID-keyed models (User, etc.) can be stored as morphable owners.
 */
return new class extends Migration
{
    protected $connection = 'pgsql';

    public function up(): void
    {
        // spatie_model_has_roles ------------------------------------------------
        // 1. Drop composite PK (includes model_id).
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_roles DROP CONSTRAINT IF EXISTS spatie_model_has_roles_pkey'
        );
        // 2. Drop composite index.
        DB::connection('pgsql')->statement(
            'DROP INDEX IF EXISTS idx_spatie_model_has_roles_model_id_model_type_23c2022a'
        );
        // 3. Change column type (cast existing bigint values to text).
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_roles ALTER COLUMN model_id TYPE varchar(255) USING model_id::varchar'
        );
        // 4. Recreate composite PK and index.
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_roles ADD PRIMARY KEY (role_id, model_id, model_type)'
        );
        DB::connection('pgsql')->statement(
            'CREATE INDEX idx_spatie_model_has_roles_model_id_model_type_23c2022a
             ON spatie_model_has_roles (model_id, model_type)'
        );

        // spatie_model_has_permissions ------------------------------------------
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_permissions DROP CONSTRAINT IF EXISTS spatie_model_has_permissions_pkey'
        );
        DB::connection('pgsql')->statement(
            'DROP INDEX IF EXISTS idx_spatie_model_has_permissions_model_id_model_type_5b4ab0f0'
        );
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_permissions ALTER COLUMN model_id TYPE varchar(255) USING model_id::varchar'
        );
        DB::connection('pgsql')->statement(
            'ALTER TABLE spatie_model_has_permissions ADD PRIMARY KEY (permission_id, model_id, model_type)'
        );
        DB::connection('pgsql')->statement(
            'CREATE INDEX idx_spatie_model_has_permissions_model_id_model_type_5b4ab0f0
             ON spatie_model_has_permissions (model_id, model_type)'
        );
    }

    public function down(): void
    {
        // Reverse: cast back to bigint (will fail if any UUID values exist).
        foreach (['spatie_model_has_roles', 'spatie_model_has_permissions'] as $table) {
            DB::connection('pgsql')->statement(
                "ALTER TABLE {$table} ALTER COLUMN model_id TYPE bigint USING model_id::bigint"
            );
        }
    }
};
