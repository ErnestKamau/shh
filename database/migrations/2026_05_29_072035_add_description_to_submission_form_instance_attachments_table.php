<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instance_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_form_instance_attachments', 'description')) {
                $table->text('description')->nullable()->after('attachment_heading');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instance_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_instance_attachments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
