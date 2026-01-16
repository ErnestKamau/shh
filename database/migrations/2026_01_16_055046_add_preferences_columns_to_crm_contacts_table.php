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
            $table->boolean('can_receive_schedule_of_analysis')->default(false)->after('receive_report');
            $table->boolean('can_receive_payment_reminders')->default(false)->after('can_receive_schedule_of_analysis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->dropColumn(['can_receive_schedule_of_analysis', 'can_receive_payment_reminders']);
        });
    }
};
