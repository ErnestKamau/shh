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
        Schema::table('submission_form_instance_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_form_instance_attachments', 'attachment_type')) {
                $table->string('attachment_type')->nullable();
            }
            if (!Schema::hasColumn('submission_form_instance_attachments', 'attachment_heading')) {
                $table->string('attachment_heading')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_form_instance_attachments', function (Blueprint $table) {
            $table->dropColumn(['attachment_type', 'attachment_heading']);
        });
    }
};
