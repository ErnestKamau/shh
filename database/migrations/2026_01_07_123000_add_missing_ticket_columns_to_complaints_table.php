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
            if (!Schema::hasColumn('complaints', 'ticket_no')) {
                $table->string('ticket_no')->nullable()->unique()->after('complaint_id');
            }
            if (!Schema::hasColumn('complaints', 'created_by')) {
                $table->string('created_by')->nullable()->after('registered_by');
            }
            if (!Schema::hasColumn('complaints', 'time_created')) {
                $table->dateTime('time_created')->nullable()->after('date');
            }
            if (!Schema::hasColumn('complaints', 'client_id')) {
                $table->unsignedBigInteger('client_id')->default(0)->after('submitted_from');
            }
            if (!Schema::hasColumn('complaints', 'customer')) {
                $table->string('customer')->nullable()->after('client_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'ticket_no')) {
                $table->dropColumn('ticket_no');
            }
            if (Schema::hasColumn('complaints', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('complaints', 'time_created')) {
                $table->dropColumn('time_created');
            }
            if (Schema::hasColumn('complaints', 'client_id')) {
                $table->dropColumn('client_id');
            }
            if (Schema::hasColumn('complaints', 'customer')) {
                $table->dropColumn('customer');
            }
        });
    }
};
