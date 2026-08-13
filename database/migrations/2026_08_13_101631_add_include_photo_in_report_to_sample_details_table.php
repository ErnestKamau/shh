<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->boolean('include_photo_in_report')->default(false)->after('photo_url');
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropColumn('include_photo_in_report');
        });
    }
};
