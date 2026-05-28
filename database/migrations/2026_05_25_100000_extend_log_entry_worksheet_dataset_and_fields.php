<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('log_entry_worksheet_columns')) {
            Schema::table('log_entry_worksheet_columns', function (Blueprint $table) {
                if (! Schema::hasColumn('log_entry_worksheet_columns', 'dataset_config')) {
                    $table->json('dataset_config')->nullable()->after('model_tied_to');
                }
            });
        }

        if (Schema::hasTable('log_entry_worksheet_mandatory_fields')) {
            Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table) {
                if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'field_options')) {
                    $table->json('field_options')->nullable()->after('model_tied_to');
                }
                if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'default_current_date')) {
                    $table->boolean('default_current_date')->default(false)->after('field_options');
                }
                if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'default_authenticated_user')) {
                    $table->boolean('default_authenticated_user')->default(false)->after('default_current_date');
                }
                if (! Schema::hasColumn('log_entry_worksheet_mandatory_fields', 'dataset_config')) {
                    $table->json('dataset_config')->nullable()->after('default_authenticated_user');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('log_entry_worksheet_mandatory_fields')) {
            Schema::table('log_entry_worksheet_mandatory_fields', function (Blueprint $table) {
                foreach (['dataset_config', 'default_authenticated_user', 'default_current_date', 'field_options'] as $col) {
                    if (Schema::hasColumn('log_entry_worksheet_mandatory_fields', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('log_entry_worksheet_columns')) {
            Schema::table('log_entry_worksheet_columns', function (Blueprint $table) {
                if (Schema::hasColumn('log_entry_worksheet_columns', 'dataset_config')) {
                    $table->dropColumn('dataset_config');
                }
            });
        }
    }
};
