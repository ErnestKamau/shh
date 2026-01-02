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
            $table->boolean('can_submit_sample')->default(0)->after('can_login');
            $table->string('signature')->nullable()->after('can_submit_sample');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->dropColumn(['can_submit_sample', 'signature']);
        });
    }
};
