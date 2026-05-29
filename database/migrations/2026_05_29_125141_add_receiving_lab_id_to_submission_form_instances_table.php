<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            $table->uuid('receiving_lab_id')->nullable()->after('review_notes');
            $table->foreign('receiving_lab_id')
                ->references('id')
                ->on('labs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            $table->dropForeign(['receiving_lab_id']);
            $table->dropColumn('receiving_lab_id');
        });
    }
};
