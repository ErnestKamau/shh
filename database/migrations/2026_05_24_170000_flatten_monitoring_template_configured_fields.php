<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_template_configured_fields', function (Blueprint $table) {
            $table->uuid('template_id')->nullable()->after('id');
            $table->string('placement', 16)->nullable()->after('template_id');
            $table->json('field_config')->nullable()->after('model_tied_to');
        });

        if (Schema::hasTable('monitoring_template_configured_field_sets')) {
            $rows = DB::table('monitoring_template_configured_fields as f')
                ->join('monitoring_template_configured_field_sets as s', 'f.field_set_id', '=', 's.id')
                ->select('f.id', 's.template_id', 's.placement')
                ->get();

            foreach ($rows as $row) {
                DB::table('monitoring_template_configured_fields')
                    ->where('id', $row->id)
                    ->update([
                        'template_id' => $row->template_id,
                        'placement' => $row->placement,
                    ]);
            }
        }

        Schema::table('monitoring_template_configured_fields', function (Blueprint $table) {
            $table->dropForeign(['field_set_id']);
            $table->dropUnique('monitoring_template_cfg_fields_set_value_unique');
            $table->dropColumn('field_set_id');
        });

        Schema::dropIfExists('monitoring_template_configured_field_sets');

        Schema::table('monitoring_template_configured_fields', function (Blueprint $table) {
            $table->uuid('template_id')->nullable(false)->change();
            $table->string('placement', 16)->nullable(false)->change();

            $table->foreign('template_id')
                ->references('id')
                ->on('monitoring_templates')
                ->cascadeOnDelete();

            $table->unique(
                ['template_id', 'placement', 'field_value_name'],
                'monitoring_template_cfg_fields_tpl_place_value_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::create('monitoring_template_configured_field_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->index();
            $table->string('name');
            $table->string('placement', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('monitoring_templates')
                ->cascadeOnDelete();
        });

        Schema::table('monitoring_template_configured_fields', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropUnique('monitoring_template_cfg_fields_tpl_place_value_unique');
            $table->dropColumn(['template_id', 'placement', 'field_config']);
            $table->uuid('field_set_id')->nullable()->after('id');
        });
    }
};
