<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Allow user_id to be nullable in ticket_change_history for API/system updates
     */
    public function up(): void
    {
        // Drop the foreign key first
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Make user_id nullable
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->change();
        });

        // Re-add foreign key with onDelete set null
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the modified foreign key
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Update null values to a default before making it not nullable
        DB::table('ticket_change_history')
            ->whereNull('user_id')
            ->update(['user_id' => 1]); // Use first user as fallback

        // Make user_id not nullable again
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable(false)->change();
        });

        // Re-add original foreign key
        Schema::table('ticket_change_history', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
