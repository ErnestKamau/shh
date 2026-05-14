<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;
    
    public function up(): void
    {
        try {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign('fk_analysis_elements_method');
            });
        } catch (\Throwable $e) {
            // Constraint may not exist yet.
        }

        try {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign('fk_analysis_elements_ltm_method_id');
            });
        } catch (\Throwable $e) {
            // Constraint may not exist yet.
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->uuid('method')->nullable()->change();
            $table->uuid('ltm_method_id')->nullable()->change();
        });

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->foreign('method', 'fk_analysis_elements_method')
                ->references('id')
                ->on('analysis_methods')
                ->nullOnDelete();

            $table->foreign('ltm_method_id', 'fk_analysis_elements_ltm_method_id')
                ->references('id')
                ->on('analysis_methods')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        try {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign('fk_analysis_elements_method');
            });
        } catch (\Throwable $e) {
            // Constraint may already be absent.
        }

        try {
            Schema::table('analysis_elements', function (Blueprint $table): void {
                $table->dropForeign('fk_analysis_elements_ltm_method_id');
            });
        } catch (\Throwable $e) {
            // Constraint may already be absent.
        }

        Schema::table('analysis_elements', function (Blueprint $table): void {
            $table->integer('method')->nullable()->change();
            $table->integer('ltm_method_id')->nullable()->change();
        });
    }
};