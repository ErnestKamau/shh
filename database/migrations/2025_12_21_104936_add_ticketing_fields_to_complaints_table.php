<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Soft deletes
            if (!Schema::hasColumn('complaints', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Submission source
            if (!Schema::hasColumn('complaints', 'submitted_from')) {
                $table->enum('submitted_from', ['ticketing_system', 'jasiri_lims', 'other'])
                    ->default('ticketing_system');
            }
            
            // Ticket category
            if (!Schema::hasColumn('complaints', 'ticket_category_id')) {
                $table->unsignedBigInteger('ticket_category_id')->nullable();
                $table->foreign('ticket_category_id')->references('id')->on('ticket_categories')->onDelete('set null');
            }
            
            // Internal note flag (for comments)
            if (!Schema::hasColumn('complaints', 'is_internal_note')) {
                $table->boolean('is_internal_note')->default(0);
            }
            
            // Escalation fields
            if (!Schema::hasColumn('complaints', 'escalated_to_user_id')) {
                $table->bigInteger('escalated_to_user_id')->nullable();
                $table->foreign('escalated_to_user_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('complaints', 'escalated_from_user_id')) {
                $table->bigInteger('escalated_from_user_id')->nullable();
                $table->foreign('escalated_from_user_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('complaints', 'escalation_reason')) {
                $table->text('escalation_reason')->nullable();
            }
        });
        
        // Ensure ticket_no has unique index (only if column exists)
        if (Schema::hasColumn('complaints', 'ticket_no')) {
            // Check if unique index doesn't already exist
            $indexes = DB::select("SHOW INDEX FROM complaints WHERE Key_name = 'complaints_ticket_no_unique'");
            if (empty($indexes)) {
                try {
                    DB::statement('CREATE UNIQUE INDEX complaints_ticket_no_unique ON complaints(ticket_no)');
                } catch (\Exception $e) {
                    // Index creation failed (might have duplicates), skip silently
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'escalation_reason')) {
                $table->dropColumn('escalation_reason');
            }
            if (Schema::hasColumn('complaints', 'escalated_from_user_id')) {
                $table->dropForeign(['escalated_from_user_id']);
                $table->dropColumn('escalated_from_user_id');
            }
            if (Schema::hasColumn('complaints', 'escalated_to_user_id')) {
                $table->dropForeign(['escalated_to_user_id']);
                $table->dropColumn('escalated_to_user_id');
            }
            if (Schema::hasColumn('complaints', 'is_internal_note')) {
                $table->dropColumn('is_internal_note');
            }
            if (Schema::hasColumn('complaints', 'ticket_category_id')) {
                $table->dropForeign(['ticket_category_id']);
                $table->dropColumn('ticket_category_id');
            }
            if (Schema::hasColumn('complaints', 'submitted_from')) {
                $table->dropColumn('submitted_from');
            }
            if (Schema::hasColumn('complaints', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
        
        // Drop unique index if exists
        $indexes = DB::select("SHOW INDEX FROM complaints WHERE Key_name = 'complaints_ticket_no_unique'");
        if (!empty($indexes)) {
            try {
                DB::statement('DROP INDEX complaints_ticket_no_unique ON complaints');
            } catch (\Exception $e) {
                // Index doesn't exist, skip
            }
        }
    }
};
