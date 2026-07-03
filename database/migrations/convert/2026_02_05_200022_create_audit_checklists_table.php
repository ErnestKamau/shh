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
        if (Schema::hasTable('audit_checklists')) {
            return;
        }
        Schema::create('audit_checklists', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->uuid('audit_type_id')->nullable()->index('idx_audit_checklists_audit_type_id_ba303152');
            $table->string('iso_standard')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable()->index('idx_audit_checklists_created_by_fe021f8f');
            $table->uuid('company_id')->nullable()->index('idx_audit_checklists_company_id_e1e13882');
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklists');
    }
};
