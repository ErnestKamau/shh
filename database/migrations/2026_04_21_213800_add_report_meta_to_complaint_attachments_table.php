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
        Schema::table('complaintattachments', function (Blueprint $table) {
            $table->string('report_number')->nullable()->after('file_path');
            $table->integer('version')->default(1)->after('report_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintattachments', function (Blueprint $table) {
            $table->dropColumn(['report_number', 'version']);
        });
    }
};
