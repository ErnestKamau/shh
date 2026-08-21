<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('request_entities', 'email_body')) {
                $table->longText('email_body')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (Schema::hasColumn('request_entities', 'email_body')) {
                $table->dropColumn('email_body');
            }
        });
    }
};
