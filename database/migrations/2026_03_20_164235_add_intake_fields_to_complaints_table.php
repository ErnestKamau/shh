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
            if (!Schema::hasColumn('complaints', 'organization_name')) {
                $table->string('organization_name')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'contact_name')) {
                $table->string('contact_name')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'title_position')) {
                $table->string('title_position')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'test_item')) {
                $table->string('test_item')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'report_serial_no')) {
                $table->string('report_serial_no')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'organization_name',
                'contact_name',
                'title_position',
                'test_item',
                'report_serial_no',
            ] as $col) {
                if (Schema::hasColumn('complaints', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
