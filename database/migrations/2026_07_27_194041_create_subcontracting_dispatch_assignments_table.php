<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subcontracting_dispatch_assignments')) {
            return;
        }

        Schema::create('subcontracting_dispatch_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sample_submission_request_id')->index();
            $table->uuid('lab_id')->index();
            $table->uuid('analysis_element_id')->index();
            $table->uuid('sample_header_id')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['sample_submission_request_id', 'analysis_element_id'],
                'subcontract_dispatch_request_element_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontracting_dispatch_assignments');
    }
};
