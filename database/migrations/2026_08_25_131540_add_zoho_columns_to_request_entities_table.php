<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('request_entities', 'zoho_id')) {
                $table->string('zoho_id', 100)->nullable();
            }
            if (! Schema::hasColumn('request_entities', 'zoho_status')) {
                $table->string('zoho_status', 50)->nullable();
            }
            if (! Schema::hasColumn('request_entities', 'errors')) {
                $table->text('errors')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (Schema::hasColumn('request_entities', 'errors')) {
                $table->dropColumn('errors');
            }
            if (Schema::hasColumn('request_entities', 'zoho_status')) {
                $table->dropColumn('zoho_status');
            }
            if (Schema::hasColumn('request_entities', 'zoho_id')) {
                $table->dropColumn('zoho_id');
            }
        });
    }
};
