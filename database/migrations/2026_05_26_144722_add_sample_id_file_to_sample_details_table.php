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
            if (! Schema::hasColumn('sample_details', 'sample_id_file')) {
                $after = Schema::hasColumn('sample_details', 'file_no') ? 'file_no' : 'sample_code';
                $table->string('sample_id_file', 255)->nullable()->after($after);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'sample_id_file')) {
                $table->dropColumn('sample_id_file');
            }
        });
    }
};
