<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->uuid('sample_type_id')->nullable()->after('sample_header_id');
            $table->index('sample_type_id', 'idx_sample_details_sample_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropIndex('idx_sample_details_sample_type_id');
            $table->dropColumn('sample_type_id');
        });
    }
};
