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
        if (Schema::hasTable('verification_closure_statuses')) {
            return;
        }
        Schema::create('verification_closure_statuses', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_verification_closure_statuses_company_id_4a517ce1');
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
        Schema::dropIfExists('verification_closure_statuses');
    }
};
