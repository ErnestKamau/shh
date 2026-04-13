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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('organization_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('title_position')->nullable();
            $table->string('test_item')->nullable();
            $table->string('report_serial_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'organization_name',
                'contact_name',
                'title_position',
                'test_item',
                'report_serial_no',
            ]);
        });
    }
};
