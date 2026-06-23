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
        Schema::table('company_report_logos', function (Blueprint $table) {
            $table->string('position_vertical')->default('top')->after('logo_path');   // top | bottom
            $table->string('position_horizontal')->default('left')->after('position_vertical'); // left | right
            $table->boolean('show_on_every_page')->default(true)->after('position_horizontal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_report_logos', function (Blueprint $table) {
            $table->dropColumn(['position_vertical', 'position_horizontal', 'show_on_every_page']);
        });
    }
};
