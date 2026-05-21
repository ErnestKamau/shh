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
            $table->text('email')->change();
            $table->text('telephone')->change();
            $table->text('mobile')->change()->nullable();
            $table->text('job_occupation')->change()->nullable();
            $table->text('unit_name')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customer_contacts', function (Blueprint $table) {
            $table->string('email', 255)->change();
            $table->string('telephone', 255)->change();
            $table->string('mobile', 255)->change()->nullable();
            $table->string('job_occupation', 255)->change()->nullable();
            $table->string('unit_name', 255)->change()->nullable();
        });
    }
};
