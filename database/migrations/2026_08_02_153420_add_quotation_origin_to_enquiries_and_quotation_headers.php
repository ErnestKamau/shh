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
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->uuid('created_from_quotation_header_id')
                ->nullable()
                ->index();
            $table->uuid('quotation_creation_token')
                ->nullable()
                ->unique();
        });

        Schema::table('quotation_headers', function (Blueprint $table): void {
            $table->uuid('source_quotation_header_id')
                ->nullable()
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            $table->dropColumn('source_quotation_header_id');
        });

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            $table->dropUnique(['quotation_creation_token']);
            $table->dropColumn([
                'created_from_quotation_header_id',
                'quotation_creation_token',
            ]);
        });
    }
};
