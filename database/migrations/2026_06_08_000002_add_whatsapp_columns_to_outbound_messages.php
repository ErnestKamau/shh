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
        Schema::table('outbound_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('outbound_messages', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('provider_template_id')->index();
            }
            if (!Schema::hasColumn('outbound_messages', 'campaign_id')) {
                $table->unsignedBigInteger('campaign_id')->nullable()->after('template_id')->index();
            }
            if (!Schema::hasColumn('outbound_messages', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('outbound_messages', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('queued_at');
            }
            if (!Schema::hasColumn('outbound_messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('outbound_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('outbound_messages', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('read_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbound_messages', function (Blueprint $table) {
            $table->dropColumn([
                'template_id',
                'campaign_id',
                'queued_at',
                'sent_at',
                'delivered_at',
                'read_at',
                'failed_at',
            ]);
        });
    }
};
