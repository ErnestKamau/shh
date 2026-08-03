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
        Schema::table('submission_form_instances', function (Blueprint $table) {
            $table->string('template_version', 10)->nullable()->after('submission_form_id');
            $table->json('structure_snapshot')->nullable()->after('template_version');
            $table->timestamp('structure_snapshot_at')->nullable()->after('structure_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            $table->dropColumn([
                'template_version',
                'structure_snapshot',
                'structure_snapshot_at',
            ]);
        });
    }
};
