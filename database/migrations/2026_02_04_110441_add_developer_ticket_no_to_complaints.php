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
        if (Schema::hasColumn('complaints', 'developer_ticket_no')) {
            return;
        }

        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'developer_ticket_id')) {
                $table->string('developer_ticket_no')->nullable()->after('developer_ticket_id');

                return;
            }

            if (Schema::hasColumn('complaints', 'ticket_no')) {
                $table->string('developer_ticket_no')->nullable()->after('ticket_no');

                return;
            }

            $table->string('developer_ticket_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('complaints', 'developer_ticket_no')) {
            return;
        }

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('developer_ticket_no');
        });
    }
};
