<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotation_headers', 'customer_acceptance_signature')) {
                $table->longText('customer_acceptance_signature')->nullable();
            }
            if (! Schema::hasColumn('quotation_headers', 'customer_acceptance_signer_name')) {
                $table->string('customer_acceptance_signer_name')->nullable();
            }
            if (! Schema::hasColumn('quotation_headers', 'customer_acceptance_signed_at')) {
                $table->timestamp('customer_acceptance_signed_at')->nullable();
            }
            if (! Schema::hasColumn('quotation_headers', 'customer_acceptance_contact_id')) {
                $table->uuid('customer_acceptance_contact_id')->nullable();
            }
            if (! Schema::hasColumn('quotation_headers', 'customer_acceptance_channel')) {
                $table->string('customer_acceptance_channel', 32)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table): void {
            $columns = [
                'customer_acceptance_signature',
                'customer_acceptance_signer_name',
                'customer_acceptance_signed_at',
                'customer_acceptance_contact_id',
                'customer_acceptance_channel',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('quotation_headers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
