<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_logs', function (Blueprint $table): void {
            $table->string('operator_id', 255)->nullable()->change();
            $table->string('edit_by', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('verification_logs', function (Blueprint $table): void {
            $table->integer('operator_id')->nullable(false)->change();
            $table->integer('edit_by')->nullable()->change();
        });
    }
};
