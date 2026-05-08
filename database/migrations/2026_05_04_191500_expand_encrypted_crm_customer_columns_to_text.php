<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_customers', function (Blueprint $table): void {
            $table->text('code')->change();
            $table->text('postal_address')->change();
            $table->text('physical_address')->change();
            $table->text('email')->change();
            $table->text('telephone1')->change();
            $table->text('telephone2')->change();
        });
    }

    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table): void {
            $table->string('code', 255)->change();
            $table->string('postal_address', 255)->change();
            $table->string('physical_address', 255)->change();
            $table->string('email', 255)->change();
            $table->string('telephone1', 255)->change();
            $table->string('telephone2', 255)->change();
        });
    }
};
