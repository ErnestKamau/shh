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
        if (!Schema::connection('pgsql_ai')->hasTable('ai_indexing_errors')) {
            Schema::connection('pgsql_ai')->create('ai_indexing_errors', function (Blueprint $table) {
                $table->id();
                $table->uuid('run_id')->nullable();
                $table->string('entity_type')->index();
                $table->string('record_id')->index();
                $table->string('source_name')->nullable();
                $table->string('error_type')->nullable();
                $table->text('error_message');
                $table->jsonb('payload_excerpt')->nullable();
                $table->unsignedBigInteger('company_id')->index()->nullable();
                $table->timestamps();
            });
        }
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->dropIfExists('ai_indexing_errors');
    }
};
