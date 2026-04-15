<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyRoleColumns = Schema::hasTable('roles')
            ? Schema::getColumnListing('roles')
            : [];

        if (! Schema::hasColumn('spatie_roles', 'description')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->string('description')->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('spatie_roles', 'level')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->integer('level')->default(1)->after('description');
            });
        }

        if (! Schema::hasColumn('spatie_roles', 'company_id')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('guard_name');
            });
        }

        if (! Schema::hasColumn('spatie_roles', 'active')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('company_id');
            });
        }

        if (empty($legacyRoleColumns) || ! in_array('name', $legacyRoleColumns, true)) {
            return;
        }

        $selectColumns = ['name'];
        foreach (['description', 'level', 'company_id', 'active'] as $optionalColumn) {
            if (in_array($optionalColumn, $legacyRoleColumns, true)) {
                $selectColumns[] = $optionalColumn;
            }
        }

        $legacyRoles = DB::table('roles')
            ->select($selectColumns)
            ->get();

        foreach ($legacyRoles as $legacyRole) {
            $payload = [];

            if (property_exists($legacyRole, 'description')) {
                $payload['description'] = $legacyRole->description;
            }

            if (property_exists($legacyRole, 'level')) {
                $payload['level'] = $legacyRole->level ?? 1;
            }

            if (property_exists($legacyRole, 'company_id')) {
                $payload['company_id'] = $legacyRole->company_id;
            }

            if (property_exists($legacyRole, 'active')) {
                $payload['active'] = $legacyRole->active ?? 1;
            }

            if (! empty($payload)) {
                DB::table('spatie_roles')
                    ->where('name', $legacyRole->name)
                    ->update($payload);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('spatie_roles', 'active')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }

        if (Schema::hasColumn('spatie_roles', 'company_id')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasColumn('spatie_roles', 'level')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }

        if (Schema::hasColumn('spatie_roles', 'description')) {
            Schema::table('spatie_roles', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
