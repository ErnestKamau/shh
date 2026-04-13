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
            $table->json('closure_recipient_emails')->nullable()->after('reject_workflow');
            $table->dateTime('closure_sent_at')->nullable()->after('closure_recipient_emails');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['closure_recipient_emails', 'closure_sent_at']);
        });
    }
};
