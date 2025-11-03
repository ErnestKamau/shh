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
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            $table->foreign('certificate_template_section_id', 'ct_elem_holders_section_fk')
                ->references('id')
                ->on('certificate_template_sections')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            $table->dropForeign('ct_elem_holders_section_fk');
        });
    }
};
