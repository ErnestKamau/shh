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

        $legacyRoles = DB::table('roles')
            ->select('name', 'description', 'level', 'company_id', 'active')
            ->get();

        foreach ($legacyRoles as $legacyRole) {
            DB::table('spatie_roles')
                ->where('name', $legacyRole->name)
                ->update([
                    'description' => $legacyRole->description,
                    'level' => $legacyRole->level ?? 1,
                    'company_id' => $legacyRole->company_id,
                    'active' => $legacyRole->active ?? 1,
                ]);
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
