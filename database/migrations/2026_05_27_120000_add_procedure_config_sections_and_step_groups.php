<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('procedure_config_field_sections')) {
            Schema::create('procedure_config_field_sections', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('procedure_worksheet_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('procedure_config_fields') && ! Schema::hasColumn('procedure_config_fields', 'procedure_config_field_section_id')) {
            Schema::table('procedure_config_fields', function (Blueprint $table) {
                $table->uuid('procedure_config_field_section_id')->nullable()->after('procedure_worksheet_id')->index();
            });
        }

        if (! Schema::hasTable('procedure_worksheet_step_groups')) {
            Schema::create('procedure_worksheet_step_groups', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('procedure_worksheet_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('procedure_worksheet_steps') && ! Schema::hasColumn('procedure_worksheet_steps', 'procedure_worksheet_step_group_id')) {
            Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
                $table->uuid('procedure_worksheet_step_group_id')->nullable()->after('procedure_worksheet_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('procedure_worksheet_steps') && Schema::hasColumn('procedure_worksheet_steps', 'procedure_worksheet_step_group_id')) {
            Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
                $table->dropColumn('procedure_worksheet_step_group_id');
            });
        }

        Schema::dropIfExists('procedure_worksheet_step_groups');

        if (Schema::hasTable('procedure_config_fields') && Schema::hasColumn('procedure_config_fields', 'procedure_config_field_section_id')) {
            Schema::table('procedure_config_fields', function (Blueprint $table) {
                $table->dropColumn('procedure_config_field_section_id');
            });
        }

        Schema::dropIfExists('procedure_config_field_sections');
    }
};
