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
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->string('contact_position')->nullable()->after('contact_id');
            $table->string('contact_phone')->nullable()->after('contact_position');
            $table->string('usage_duration')->nullable()->after('contact_phone');
            $table->string('doc_ref')->nullable()->after('usage_duration');
            $table->string('doc_version')->nullable()->after('doc_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->dropColumn(['contact_position', 'contact_phone', 'usage_duration', 'doc_ref', 'doc_version']);
        });
    }
};
