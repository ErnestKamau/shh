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
        if (!Schema::connection('pgsql_ai')->hasTable('ai.ai_manual_documents')) {
            Schema::connection('pgsql_ai')->create('ai.ai_manual_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('collection_name');
                $table->text('content');
                $table->string('required_permission')->nullable()->default('General.View');
                $table->jsonb('metadata')->nullable();
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->dropIfExists('ai.ai_manual_documents');
    }
};
