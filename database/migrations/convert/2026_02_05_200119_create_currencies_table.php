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
        if (Schema::hasTable('currencies')) {
            return;
        }
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->unique()->comment('Currency code (BWP, EUR, USD, etc.)');
            $table->string('description')->nullable();
            $table->string('iso_code')->nullable();
            $table->string('iso_numeric_code')->nullable();
            $table->date('exchange_rate_date')->nullable();
            $table->decimal('exchange_rate_amt', 20, 10)->default(0)->comment('Exchange rate amount');
            $table->decimal('currency_factor', 20, 10)->default(0)->comment('Currency conversion factor');
            $table->boolean('emu_currency')->default(false);
            $table->string('realized_gains_acc')->nullable();
            $table->string('realized_losses_acc')->nullable();
            $table->string('unrealized_gains_acc')->nullable();
            $table->string('unrealized_losses_acc')->nullable();
            $table->string('realized_gl_gains_account')->nullable();
            $table->string('realized_gl_losses_account')->nullable();
            $table->string('residual_gains_account')->nullable();
            $table->string('residual_losses_account')->nullable();
            $table->decimal('amount_rounding_precision', 10, 5)->default(0.01);
            $table->string('amount_decimal_places')->nullable()->comment('e.g., 2:2');
            $table->decimal('invoice_rounding_precision', 10, 5)->default(0.01);
            $table->string('invoice_rounding_type')->nullable()->comment('Nearest, Up, Down');
            $table->decimal('unit_amount_rounding_precision', 10, 5)->default(1.0E-5);
            $table->string('unit_amount_decimal_places')->nullable()->comment('e.g., 2:5');
            $table->decimal('appln_rounding_precision', 10, 5)->default(0);
            $table->string('conv_lcy_rndg_debit_acc')->nullable();
            $table->string('conv_lcy_rndg_credit_acc')->nullable();
            $table->decimal('max_vat_difference_allowed', 15)->default(0);
            $table->string('vat_rounding_type')->nullable()->comment('Nearest, Up, Down');
            $table->decimal('payment_tolerance_percent', 10, 4)->default(0);
            $table->decimal('max_payment_tolerance_amount', 15)->default(0);
            $table->date('last_date_adjusted')->nullable();
            $table->date('last_date_modified')->nullable();
            $table->boolean('coupled_to_crm')->default(false);
            $table->boolean('coupled_to_dataverse')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
