<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Goods Receipt: items verifier (issue_to) confirms delivery inspection.
     */
    public function up(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('request_entities', 'requester_confirmation')) {
                $table->boolean('requester_confirmation')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('request_entities')) {
            return;
        }

        Schema::table('request_entities', function (Blueprint $table) {
            if (Schema::hasColumn('request_entities', 'requester_confirmation')) {
                $table->dropColumn('requester_confirmation');
            }
        });
    }
};
