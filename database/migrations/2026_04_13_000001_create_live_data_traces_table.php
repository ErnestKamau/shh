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
        $tableName = 'ai_live_data_traces';

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->uuid('query_id')->index();
                $table->text('query');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                
                // LiveData specific metrics
                $table->string('intent')->nullable()->index();
                $table->string('handler_used')->nullable();
                $table->string('orchestration_mode')->nullable(); // balanced, live_data_only
                $table->boolean('fallback_to_ai')->default(false);
                
                // Result info
                $table->integer('result_count')->default(0);
                $table->string('result_type')->nullable(); // data, action
                
                // Performance metrics
                $table->float('execution_time_ms')->nullable();
                
                // User context
                $table->string('user_role')->nullable();
                
                $table->timestamps();
                
                $table->index(['created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_live_data_traces');
    }
};
