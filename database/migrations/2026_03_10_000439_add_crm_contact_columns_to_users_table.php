<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add CRM contact/customer link columns to users (for client contacts with login).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'crm_contact_id')) {
                $table->unsignedBigInteger('crm_contact_id')->nullable()->after('client_id');
            }
            if (! Schema::hasColumn('users', 'crmcontact_id')) {
                $table->unsignedBigInteger('crmcontact_id')->nullable()->after('crm_contact_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'crmcontact_id')) {
                $table->dropColumn('crmcontact_id');
            }
            if (Schema::hasColumn('users', 'crm_contact_id')) {
                $table->dropColumn('crm_contact_id');
            }
        });
    }
};
