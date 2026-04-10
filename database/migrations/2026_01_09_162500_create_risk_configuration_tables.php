<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Risk Configuration Tables - For ISO-compliant, globally configurable risk management
     */
    public function up(): void
    {
        // Risk Level Thresholds Configuration (Configurable RPN ranges)
        Schema::create('risk_level_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('risk_level'); // Critical, High, Medium, Low
            $table->integer('min_rpn')->nullable(); // Minimum RPN for this level
            $table->integer('max_rpn')->nullable(); // Maximum RPN for this level
            $table->string('color_code')->default('#6c757d'); // Display color
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'is_active']);
            $table->unique(['risk_level', 'company_id']);
        });

        // Review Frequency Configuration (Configurable review frequencies by risk level)
        Schema::create('risk_review_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('risk_level'); // Critical, High, Medium, Low
            $table->integer('frequency_days'); // Review frequency in days
            $table->string('frequency_label'); // e.g., "Monthly", "Quarterly"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'is_active']);
            $table->unique(['risk_level', 'company_id']);
        });

        // Acceptance Criteria Configuration (Configurable acceptance thresholds)
        Schema::create('risk_acceptance_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Default", "High Risk Category", "Technical Risks"
            $table->string('code')->unique();
            $table->integer('threshold_rpn'); // Acceptance threshold RPN
            $table->text('criteria_description')->nullable();
            $table->boolean('requires_treatment_plan')->default(true);
            $table->boolean('requires_monitoring')->default(true);
            $table->boolean('can_skip_treatment')->default(false);
            $table->text('applicable_categories')->nullable(); // JSON array of category IDs
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'is_active']);
        });

        // Risk Scoring Configuration (Configurable scoring methods)
        Schema::create('risk_scoring_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard 5x5", "Custom 3x3", "ISO 17025"
            $table->string('code')->unique();
            $table->string('scoring_method')->default('multiplicative'); // multiplicative, additive, custom
            $table->text('formula')->nullable(); // Custom formula if needed
            $table->integer('max_likelihood_score')->default(5);
            $table->integer('max_severity_score')->default(5);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'is_active']);
        });

        // Seed default configuration data
        $this->seedDefaultConfiguration();
    }

    /**
     * Seed default configuration data
     */
    private function seedDefaultConfiguration(): void
    {
        // Default Risk Level Thresholds (ISO-compliant ranges)
        DB::table('risk_level_thresholds')->insert([
            ['risk_level' => 'Critical', 'min_rpn' => 16, 'max_rpn' => 25, 'color_code' => '#e74c3c', 'description' => 'Critical risks requiring immediate action', 'order_index' => 1, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'High', 'min_rpn' => 10, 'max_rpn' => 15, 'color_code' => '#f39c12', 'description' => 'High risks requiring prompt action', 'order_index' => 2, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'Medium', 'min_rpn' => 5, 'max_rpn' => 9, 'color_code' => '#3498db', 'description' => 'Medium risks requiring monitoring', 'order_index' => 3, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'Low', 'min_rpn' => 1, 'max_rpn' => 4, 'color_code' => '#27ae60', 'description' => 'Low risks that can be accepted or monitored', 'order_index' => 4, 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Default Review Frequencies (ISO-compliant frequencies)
        DB::table('risk_review_frequencies')->insert([
            ['risk_level' => 'Critical', 'frequency_days' => 30, 'frequency_label' => 'Monthly', 'description' => 'Critical risks reviewed monthly', 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'High', 'frequency_days' => 90, 'frequency_label' => 'Quarterly', 'description' => 'High risks reviewed quarterly', 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'Medium', 'frequency_days' => 180, 'frequency_label' => 'Semi-Annually', 'description' => 'Medium risks reviewed semi-annually', 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['risk_level' => 'Low', 'frequency_days' => 365, 'frequency_label' => 'Annually', 'description' => 'Low risks reviewed annually', 'is_active' => true, 'company_id' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Default Acceptance Criteria
        DB::table('risk_acceptance_criteria')->insert([
            [
                'name' => 'Default Acceptance Criteria',
                'code' => 'DEFAULT',
                'threshold_rpn' => 15,
                'criteria_description' => 'Default acceptance threshold for all risks',
                'requires_treatment_plan' => true,
                'requires_monitoring' => true,
                'can_skip_treatment' => false,
                'applicable_categories' => null,
                'is_default' => true,
                'is_active' => true,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // Default Scoring Configuration
        DB::table('risk_scoring_configs')->insert([
            [
                'name' => 'Standard 5x5 Multiplicative',
                'code' => 'STANDARD_5X5',
                'scoring_method' => 'multiplicative',
                'formula' => 'RPN = Likelihood × Severity',
                'max_likelihood_score' => 5,
                'max_severity_score' => 5,
                'description' => 'Standard ISO-compliant 5x5 risk scoring matrix',
                'is_default' => true,
                'is_active' => true,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_scoring_configs');
        Schema::dropIfExists('risk_acceptance_criteria');
        Schema::dropIfExists('risk_review_frequencies');
        Schema::dropIfExists('risk_level_thresholds');
    }
};

