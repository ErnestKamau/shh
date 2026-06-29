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
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'closure_recipient_emails')) {
                $table->json('closure_recipient_emails')->nullable()->after('reject_workflow');
            }
            if (!Schema::hasColumn('complaints', 'closure_sent_at')) {
                $table->dateTime('closure_sent_at')->nullable()->after('closure_recipient_emails');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'closure_recipient_emails')) {
                $table->dropColumn('closure_recipient_emails');
            }
            if (Schema::hasColumn('complaints', 'closure_sent_at')) {
                $table->dropColumn('closure_sent_at');
            }
        });
    }
};
