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
        Schema::table('batch_attachments', function (Blueprint $table) {
            // In PostgreSQL, changing from integer to UUID requires explicit casting
            DB::statement('ALTER TABLE batch_attachments ALTER COLUMN attachment_type TYPE UUID USING attachment_type::text::uuid');
            DB::statement('ALTER TABLE batch_attachments ALTER COLUMN uploaded_by TYPE UUID USING uploaded_by::text::uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_attachments', function (Blueprint $table) {
            DB::statement('ALTER TABLE batch_attachments ALTER COLUMN attachment_type TYPE INTEGER USING attachment_type::text::integer');
            DB::statement('ALTER TABLE batch_attachments ALTER COLUMN uploaded_by TYPE INTEGER USING uploaded_by::text::integer');
        });
    }
};
