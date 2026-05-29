<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_details', 'customer_sample_id')) {
                $after = Schema::hasColumn('sample_details', 'barcode') ? 'barcode' : 'sample_code';
                $table->string('customer_sample_id', 255)->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            if (Schema::hasColumn('sample_details', 'customer_sample_id')) {
                $table->dropColumn('customer_sample_id');
            }
        });
    }
};
