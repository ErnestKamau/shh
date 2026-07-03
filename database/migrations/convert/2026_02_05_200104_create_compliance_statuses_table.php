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
        if (Schema::hasTable('compliance_statuses')) {
            return;
        }
        Schema::create('compliance_statuses', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->string('badge_class')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_compliance_statuses_company_id_f46badc2');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active'], 'idx_compliance_statuses_company_id_is_active_56adba3c');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_statuses');
    }
};
