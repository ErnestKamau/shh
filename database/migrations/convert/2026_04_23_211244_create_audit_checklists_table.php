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
        Schema::create('audit_checklists', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->uuid('audit_type_id')->nullable()->index('audit_checklists_audit_type_id_foreign');
            $table->string('iso_standard')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_audit_checklists_company_id_3a5ac149');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['audit_type_id'], 'fk_audit_checklists_audit_type_id_78022d85')->references(['id'])->on('audit_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_checklists_company_id_52b583e1')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
