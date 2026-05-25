<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_sections', function (Blueprint $table) {
            $table->unsignedTinyInteger('reading_frequency')->nullable()->after('result_nature');
            $table->decimal('reading_frequency_interval', 8, 2)->nullable()->after('reading_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('lab_sections', function (Blueprint $table) {
            $table->dropColumn(['reading_frequency', 'reading_frequency_interval']);
        });
    }
};
