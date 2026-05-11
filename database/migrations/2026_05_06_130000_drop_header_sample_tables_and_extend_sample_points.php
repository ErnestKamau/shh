<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sample_points')) {
            Schema::table('sample_points', function (Blueprint $table) {
                if (!Schema::hasColumn('sample_points', 'code')) {
                    $table->string('code')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'name')) {
                    $table->string('name')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'created_by')) {
                    $table->uuid('created_by')->nullable();
                }
            });

            if (Schema::hasTable('crm_sample_points') && Schema::hasColumn('sample_points', 'crm_sample_point_id')) {
                DB::statement("\n                    UPDATE sample_points sp\n                    SET name = COALESCE(sp.name, csp.name),\n                        code = COALESCE(sp.code, csp.code),\n                        created_by = COALESCE(sp.created_by, csp.created_by),\n                        description = COALESCE(sp.description, csp.code)\n                    FROM crm_sample_points csp\n                    WHERE sp.crm_sample_point_id = csp.id\n                ");
            }

            if (Schema::hasTable('sample_point_area') && Schema::hasColumn('sample_points', 'sample_point_area_id')) {
                DB::statement("\n                    UPDATE sample_points sp\n                    SET description = COALESCE(sp.description, spa.description)\n                    FROM sample_point_area spa\n                    WHERE sp.sample_point_area_id = spa.id\n                ");
            }

            DB::statement("\n                UPDATE sample_points\n                SET name = COALESCE(name, 'Sample Point')\n                WHERE name IS NULL OR name = ''\n            ");
        }

        DB::statement('DROP TABLE IF EXISTS sample_point_area CASCADE');
        DB::statement('DROP TABLE IF EXISTS crm_sample_points CASCADE');
        DB::statement('DROP TABLE IF EXISTS crm_areas CASCADE');
    }

    public function down(): void
    {
        if (Schema::hasTable('sample_points')) {
            Schema::table('sample_points', function (Blueprint $table) {
                if (!Schema::hasColumn('sample_points', 'crm_sample_point_id')) {
                    $table->uuid('crm_sample_point_id')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'crm_area_id')) {
                    $table->uuid('crm_area_id')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'sample_point_area_id')) {
                    $table->uuid('sample_point_area_id')->nullable();
                }
                if (!Schema::hasColumn('sample_points', 'crm_company_sub_unit_id')) {
                    $table->uuid('crm_company_sub_unit_id')->nullable();
                }
                if (Schema::hasColumn('sample_points', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('sample_points', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('sample_points', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('sample_points', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }

        if (!Schema::hasTable('crm_areas')) {
            Schema::create('crm_areas', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->uuid('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_sample_points')) {
            Schema::create('crm_sample_points', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->uuid('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sample_point_area')) {
            Schema::create('sample_point_area', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('description')->nullable();
                $table->boolean('active')->default(true);
                $table->uuid('crm_customer_id')->nullable();
                $table->uuid('crm_company_sub_unit_id')->nullable();
                $table->uuid('crm_area_id')->nullable();
                $table->uuid('crm_company_unit_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
};
