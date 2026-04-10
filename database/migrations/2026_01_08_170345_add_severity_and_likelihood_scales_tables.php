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
        // Severity Scales (for risk assessment)
        if (!Schema::hasTable('severity_scales')) {
            Schema::create('severity_scales', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->integer('score')->default(1);
                $table->text('description')->nullable();
                $table->string('color_code')->nullable();
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('company_id')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed default severity scales
            \DB::table('severity_scales')->insert([
                ['name' => 'Very Low', 'code' => 'SEV-1', 'score' => 1, 'description' => 'Very low severity', 'color_code' => '#28a745', 'order_index' => 1, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Low', 'code' => 'SEV-2', 'score' => 2, 'description' => 'Low severity', 'color_code' => '#6c757d', 'order_index' => 2, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Medium', 'code' => 'SEV-3', 'score' => 3, 'description' => 'Medium severity', 'color_code' => '#ffc107', 'order_index' => 3, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'High', 'code' => 'SEV-4', 'score' => 4, 'description' => 'High severity', 'color_code' => '#fd7e14', 'order_index' => 4, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Very High', 'code' => 'SEV-5', 'score' => 5, 'description' => 'Very high severity', 'color_code' => '#dc3545', 'order_index' => 5, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Likelihood Scales (for risk assessment)
        if (!Schema::hasTable('likelihood_scales')) {
            Schema::create('likelihood_scales', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->integer('score')->default(1);
                $table->text('description')->nullable();
                $table->string('color_code')->nullable();
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('company_id')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed default likelihood scales
            \DB::table('likelihood_scales')->insert([
                ['name' => 'Very Unlikely', 'code' => 'LIK-1', 'score' => 1, 'description' => 'Very unlikely to occur', 'color_code' => '#28a745', 'order_index' => 1, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Unlikely', 'code' => 'LIK-2', 'score' => 2, 'description' => 'Unlikely to occur', 'color_code' => '#6c757d', 'order_index' => 2, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Possible', 'code' => 'LIK-3', 'score' => 3, 'description' => 'Possible to occur', 'color_code' => '#ffc107', 'order_index' => 3, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Likely', 'code' => 'LIK-4', 'score' => 4, 'description' => 'Likely to occur', 'color_code' => '#fd7e14', 'order_index' => 4, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Very Likely', 'code' => 'LIK-5', 'score' => 5, 'description' => 'Very likely to occur', 'color_code' => '#dc3545', 'order_index' => 5, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likelihood_scales');
        Schema::dropIfExists('severity_scales');
    }
};
