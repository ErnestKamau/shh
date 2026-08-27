<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quotation_header_lab_sections')) {
            return;
        }

        Schema::create('quotation_header_lab_sections', function (Blueprint $table): void {
            $table->uuid('quotation_header_id');
            $table->uuid('lab_section_id');
            $table->timestamps();

            $table->primary(
                ['quotation_header_id', 'lab_section_id'],
                'quotation_header_lab_sections_primary',
            );
            $table->index('quotation_header_id');
            $table->index('lab_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_header_lab_sections');
    }
};
