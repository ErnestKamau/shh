<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend the enum to include new element types
        // MySQL requires ALTER TABLE to modify enum
        DB::statement("ALTER TABLE certificate_template_elements MODIFY COLUMN element_type ENUM(
            'heading', 'paragraph', 'text', 'strong_text', 
            'image', 'data_field', 'table', 'spacer',
            'button', 'divider', 'list', 'icon', 'custom_html',
            'input_text', 'input_email', 'input_number', 'input_date', 'textarea', 'select'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE certificate_template_elements MODIFY COLUMN element_type ENUM(
            'heading', 'paragraph', 'text', 'strong_text', 
            'image', 'data_field', 'table', 'spacer',
            'button', 'divider', 'list', 'icon', 'custom_html',
            'input_text', 'input_email', 'input_number', 'input_date', 'textarea', 'select'
        )");
    }
};
