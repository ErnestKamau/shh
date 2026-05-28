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
        Schema::table('sample_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sample_details', 'matrix')) {
                $table->string('matrix')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'matrix')) {
                $table->dropColumn('matrix');
            }
        });
    }
};
