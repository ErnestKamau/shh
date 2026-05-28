<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('formula_versions') && ! Schema::hasColumn('formula_versions', 'mandatory_fields_placement')) {
            Schema::table('formula_versions', function (Blueprint $table): void {
                $table->string('mandatory_fields_placement', 16)->default('bottom')->after('is_active');
            });
        }

        if (! Schema::hasTable('formula_mandatory_fields')) {
            return;
        }

        $this->expandMandatoryFieldTypeColumn();

        if (! Schema::hasColumn('formula_mandatory_fields', 'field_options')) {
            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->json('field_options')->nullable()->after('field_value_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('formula_versions') && Schema::hasColumn('formula_versions', 'mandatory_fields_placement')) {
            Schema::table('formula_versions', function (Blueprint $table): void {
                $table->dropColumn('mandatory_fields_placement');
            });
        }

        if (Schema::hasTable('formula_mandatory_fields') && Schema::hasColumn('formula_mandatory_fields', 'field_options')) {
            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->dropColumn('field_options');
            });
        }
    }

    protected function expandMandatoryFieldTypeColumn(): void
    {
        if (! Schema::hasColumn('formula_mandatory_fields', 'field_type')) {
            return;
        }

        if (Schema::hasColumn('formula_mandatory_fields', 'field_type_new')) {
            $this->completeFieldTypeColumnReplacement();

            return;
        }

        Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
            $table->string('field_type_new', 32)->nullable()->after('label');
        });

        foreach (DB::table('formula_mandatory_fields')->orderBy('created_at')->cursor() as $field) {
            DB::table('formula_mandatory_fields')
                ->where('id', $field->id)
                ->update(['field_type_new' => $field->field_type]);
        }

        Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
            $table->dropColumn('field_type');
        });

        $this->completeFieldTypeColumnReplacement();
    }

    protected function completeFieldTypeColumnReplacement(): void
    {
        if (! Schema::hasColumn('formula_mandatory_fields', 'field_type')) {
            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->string('field_type', 32)->default('input')->after('label');
            });
        }

        if (Schema::hasColumn('formula_mandatory_fields', 'field_type_new')) {
            foreach (DB::table('formula_mandatory_fields')->whereNotNull('field_type_new')->orderBy('created_at')->cursor() as $field) {
                DB::table('formula_mandatory_fields')
                    ->where('id', $field->id)
                    ->update(['field_type' => $field->field_type_new]);
            }

            Schema::table('formula_mandatory_fields', function (Blueprint $table): void {
                $table->dropColumn('field_type_new');
            });
        }

        DB::table('formula_mandatory_fields')
            ->whereNull('field_type')
            ->update(['field_type' => 'input']);
    }
};
