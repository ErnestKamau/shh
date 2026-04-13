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
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->boolean('receive_feedback')->default(0)->after('receive_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->dropColumn('receive_feedback');
        });
    }
};
