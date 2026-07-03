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
        Schema::create('root_cause_methods', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->text('template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_root_cause_methods_company_id_94601377');
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
        Schema::dropIfExists('root_cause_methods');
    }
};
