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
        Schema::create('compliance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->string('badge_class')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'code']);
        });

        \DB::table('compliance_statuses')->insert([
            [
                'name' => 'Compliant',
                'code' => 'COMPLIANT',
                'description' => 'Requirement is fully met.',
                'color_code' => '#28a745',
                'badge_class' => 'success',
                'order_index' => 1,
                'is_active' => true,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Partially Compliant',
                'code' => 'PARTIAL',
                'description' => 'Requirement is partially met.',
                'color_code' => '#ffc107',
                'badge_class' => 'warning',
                'order_index' => 2,
                'is_active' => true,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Non-Compliant',
                'code' => 'NON_COMPLIANT',
                'description' => 'Requirement is not met.',
                'color_code' => '#dc3545',
                'badge_class' => 'danger',
                'order_index' => 3,
                'is_active' => true,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_statuses');
    }
};
