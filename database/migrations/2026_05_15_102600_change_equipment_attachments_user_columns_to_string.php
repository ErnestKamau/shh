<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_attachments', function (Blueprint $table): void {
            $table->string('upload_by', 255)->change();
            $table->string('edit_by', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_attachments', function (Blueprint $table): void {
            $table->integer('upload_by')->change();
            $table->integer('edit_by')->nullable()->change();
        });
    }
};
