<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qc_scheme_rules')) {
            return;
        }

        Schema::create('qc_scheme_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('qc_scheme_id')->index();
            $table->string('rule_type', 50);
            $table->string('value', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('qc_scheme_id')
                ->references('id')
                ->on('qc_scheme')
                ->cascadeOnDelete();

            $table->unique(['qc_scheme_id', 'rule_type'], 'qc_scheme_rules_scheme_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_scheme_rules');
    }
};
