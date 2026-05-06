<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_types', function (Blueprint $table): void {
            if (!Schema::hasColumn('sample_types', 'exhibit_returned_on_reception')) {
                $table->boolean('exhibit_returned_on_reception')->default(false)->after('is_results_attachable');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_types', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_types', 'exhibit_returned_on_reception')) {
                $table->dropColumn('exhibit_returned_on_reception');
            }
        });
    }
};
