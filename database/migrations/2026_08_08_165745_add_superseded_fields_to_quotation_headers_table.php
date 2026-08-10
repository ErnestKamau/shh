<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotation_headers', 'superseded_by_quotation_header_id')) {
                $table->uuid('superseded_by_quotation_header_id')
                    ->nullable()
                    ->index()
                    ->after('revision_of_quotation_header_id');
            }
            if (! Schema::hasColumn('quotation_headers', 'superseded_at')) {
                $table->timestamp('superseded_at')
                    ->nullable()
                    ->after('superseded_by_quotation_header_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            if (Schema::hasColumn('quotation_headers', 'superseded_at')) {
                $table->dropColumn('superseded_at');
            }
            if (Schema::hasColumn('quotation_headers', 'superseded_by_quotation_header_id')) {
                $table->dropColumn('superseded_by_quotation_header_id');
            }
        });
    }
};
