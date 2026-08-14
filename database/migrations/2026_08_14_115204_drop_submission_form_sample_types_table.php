<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('submission_form_sample_types');
    }

    public function down(): void
    {
        // TRF forms are linked via submission_form_sample_type_categories only.
    }
};
