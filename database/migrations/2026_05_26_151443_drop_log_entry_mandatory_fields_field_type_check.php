<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('log_entry_worksheet_mandatory_fields')) {
            return;
        }

        if (Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_type_new')) {
            $this->completeFieldTypeColumnReplacement();

            return;
        }

        if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_type')) {
            return;
        }

        $this->replaceFieldTypeColumnWithString();
    }

    public function down(): void
    {
        if (! Schema::hasTable('log_entry_worksheet_mandatory_fields')
            || ! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_type')) {
            return;
        }

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->string('field_type_legacy', 50)->nullable()->after('label');
        });

        foreach (DB::table('log_entry_worksheet_mandatory_fields')->orderBy('created_at')->cursor() as $field) {
            DB::table('log_entry_worksheet_mandatory_fields')
                ->where('id', $field->id)
                ->update(['field_type_legacy' => $field->field_type]);
        }

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->dropColumn('field_type');
        });

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->enum('field_type', ['input', 'datetime', 'date', 'dataset_related'])->after('label');
        });

        foreach (DB::table('log_entry_worksheet_mandatory_fields')->whereNotNull('field_type_legacy')->orderBy('created_at')->cursor() as $field) {
            $legacy = $field->field_type_legacy;
            if (! in_array($legacy, ['input', 'datetime', 'date', 'dataset_related'], true)) {
                $legacy = 'input';
            }

            DB::table('log_entry_worksheet_mandatory_fields')
                ->where('id', $field->id)
                ->update(['field_type' => $legacy]);
        }

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->dropColumn('field_type_legacy');
        });
    }

    protected function replaceFieldTypeColumnWithString(): void
    {
        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->string('field_type_new', 50)->nullable()->after('label');
        });

        foreach (DB::table('log_entry_worksheet_mandatory_fields')->orderBy('created_at')->cursor() as $field) {
            DB::table('log_entry_worksheet_mandatory_fields')
                ->where('id', $field->id)
                ->update(['field_type_new' => $field->field_type]);
        }

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->dropColumn('field_type');
        });

        $this->completeFieldTypeColumnReplacement();
    }

    protected function completeFieldTypeColumnReplacement(): void
    {
        if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_type')) {
            Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
                $table->string('field_type', 50)->nullable()->after('label');
            });
        }

        foreach (DB::table('log_entry_worksheet_mandatory_fields')->whereNotNull('field_type_new')->orderBy('created_at')->cursor() as $field) {
            DB::table('log_entry_worksheet_mandatory_fields')
                ->where('id', $field->id)
                ->update(['field_type' => $field->field_type_new]);
        }

        DB::table('log_entry_worksheet_mandatory_fields')
            ->whereNull('field_type')
            ->update(['field_type' => 'input']);

        Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
            $table->string('field_type', 50)->nullable(false)->change();
        });

        if (Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_type_new')) {
            Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table): void {
                $table->dropColumn('field_type_new');
            });
        }
    }
};
