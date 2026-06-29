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
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->text('internal_remarks')->nullable()->after('send_to_customer');
        });
    }

    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->dropColumn('internal_remarks');
        });
    }
};
