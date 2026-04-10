<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Developer app ticket references
            $table->unsignedBigInteger('developer_ticket_id')->nullable()->after('ticket_no');
            $table->string('developer_ticket_no')->nullable()->after('developer_ticket_id');

            // Sync tracking
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('sync_failed')->default(false)->after('last_synced_at');
            $table->text('sync_error')->nullable()->after('sync_failed');

            // Indexes
            $table->index('developer_ticket_id');
            $table->index('last_synced_at');
            $table->index('sync_failed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['developer_ticket_id']);
            $table->dropIndex(['last_synced_at']);
            $table->dropIndex(['sync_failed']);

            $table->dropColumn([
                'developer_ticket_id',
                'developer_ticket_no',
                'last_synced_at',
                'sync_failed',
                'sync_error',
            ]);
        });
    }
};
