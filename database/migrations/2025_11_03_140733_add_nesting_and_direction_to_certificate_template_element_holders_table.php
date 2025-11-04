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
            // Add parent_holder_id for nesting holders inside other holders
            $table->foreignId('parent_holder_id')
                ->nullable()
                ->after('certificate_template_section_id')
                ->constrained('certificate_template_element_holders')
                ->onDelete('cascade');
            
            // Add direction for layout (horizontal or vertical)
            $table->enum('direction', ['horizontal', 'vertical'])
                ->default('horizontal')
                ->after('holder_type');
            
            // Add flex properties for better layout control
            $table->decimal('flex_grow', 5, 2)->default(0)->nullable()->after('height_percent');
            $table->decimal('flex_shrink', 5, 2)->default(1)->nullable()->after('flex_grow');
            $table->string('flex_basis', 50)->nullable()->after('flex_shrink'); // e.g., "200px", "50%", "auto"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_element_holders', function (Blueprint $table) {
            $table->dropForeign(['parent_holder_id']);
            $table->dropColumn([
                'parent_holder_id',
                'direction',
                'flex_grow',
                'flex_shrink',
                'flex_basis'
            ]);
        });
    }
};
