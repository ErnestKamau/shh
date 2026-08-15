<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->boolean('is_main_customer_contact')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->dropColumn('is_main_customer_contact');
        });
    }
};
