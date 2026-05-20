<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_forms', function (Blueprint $table): void {
            if (! Schema::hasColumn('submission_forms', 'is_customer_request_form')) {
                $table->boolean('is_customer_request_form')
                    ->default(false)
                    ->after('is_customer_portal_form');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table): void {
            if (Schema::hasColumn('submission_forms', 'is_customer_request_form')) {
                $table->dropColumn('is_customer_request_form');
            }
        });
    }
};
