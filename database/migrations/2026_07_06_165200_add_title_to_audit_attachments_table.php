<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_attachments') || Schema::hasColumn('audit_attachments', 'title')) {
            return;
        }

        Schema::table('audit_attachments', function (Blueprint $table) {
            $table->string('title')->nullable()->after('original_name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_attachments') || ! Schema::hasColumn('audit_attachments', 'title')) {
            return;
        }

        Schema::table('audit_attachments', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
