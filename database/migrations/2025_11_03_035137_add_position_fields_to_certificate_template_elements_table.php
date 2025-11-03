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
        Schema::table('certificate_template_elements', function (Blueprint $table) {
            // Check and add foreign key only if it doesn't exist
            if (!Schema::hasColumn('certificate_template_elements', 'certificate_template_element_holder_id')) {
                $table->foreignId('certificate_template_element_holder_id')->nullable()->after('certificate_template_section_id')->constrained('certificate_template_element_holders')->onDelete('cascade');
            }
            
            // Add position and size fields
            if (!Schema::hasColumn('certificate_template_elements', 'position_x')) {
                $table->decimal('position_x', 10, 2)->nullable()->after('sort_order');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'position_y')) {
                $table->decimal('position_y', 10, 2)->nullable()->after('position_x');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'width')) {
                $table->decimal('width', 10, 2)->nullable()->after('position_y');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('width');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'position_x_percent')) {
                $table->decimal('position_x_percent', 10, 4)->nullable()->after('height');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'position_y_percent')) {
                $table->decimal('position_y_percent', 10, 4)->nullable()->after('position_x_percent');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'width_percent')) {
                $table->decimal('width_percent', 10, 4)->nullable()->after('position_y_percent');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'height_percent')) {
                $table->decimal('height_percent', 10, 4)->nullable()->after('width_percent');
            }
            if (!Schema::hasColumn('certificate_template_elements', 'z_index')) {
                $table->integer('z_index')->default(1)->after('height_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_template_elements', function (Blueprint $table) {
            $table->dropForeign(['certificate_template_element_holder_id']);
            $table->dropColumn([
                'certificate_template_element_holder_id',
                'position_x',
                'position_y',
                'width',
                'height',
                'position_x_percent',
                'position_y_percent',
                'width_percent',
                'height_percent',
                'z_index'
            ]);
        });
    }
};
