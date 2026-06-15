<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            $table->string('laboratory_ref')->nullable()->after('quote_number');
            $table->string('subject')->nullable()->after('laboratory_ref');
            $table->uuid('sample_point_id')->nullable()->index()->after('subject');
            $table->string('sampling_location', 500)->nullable()->after('sample_point_id');
            $table->text('terms_override')->nullable()->after('payment_info');
            $table->boolean('show_loq_column')->default(true)->after('terms_override');
            $table->boolean('show_mu_column')->default(true)->after('show_loq_column');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            $table->dropColumn([
                'laboratory_ref',
                'subject',
                'sample_point_id',
                'sampling_location',
                'terms_override',
                'show_loq_column',
                'show_mu_column',
            ]);
        });
    }
};
