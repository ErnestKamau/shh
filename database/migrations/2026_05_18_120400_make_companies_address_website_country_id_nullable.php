<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->text('address')->nullable()->change();
            $table->text('website')->nullable()->change();
            $table->uuid('country_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->text('address')->nullable(false)->change();
            $table->text('website')->nullable(false)->change();
            $table->uuid('country_id')->nullable(false)->change();
        });
    }
};
