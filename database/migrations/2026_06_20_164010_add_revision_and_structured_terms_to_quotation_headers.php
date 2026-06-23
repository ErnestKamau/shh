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
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_headers', 'revision_number')) {
                $table->unsignedSmallInteger('revision_number')->default(1)->after('revision_of_quotation_header_id');
            }

            if (! Schema::hasColumn('quotation_headers', 'structured_terms')) {
                $table->json('structured_terms')->nullable()->after('terms_override');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_headers', 'structured_terms')) {
                $table->dropColumn('structured_terms');
            }

            if (Schema::hasColumn('quotation_headers', 'revision_number')) {
                $table->dropColumn('revision_number');
            }
        });
    }
};
