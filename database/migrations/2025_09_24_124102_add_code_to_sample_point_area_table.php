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
        Schema::table('sample_point_area', function (Blueprint $table) {
            $table->string('code')->after('name');
            $table->string('description')->nullable()->after('code');
            $table->boolean('active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_point_area', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'active']);
        });
    }
};
