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
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('is_kb_indexed')->default(false)->index();
            $table->string('kb_collection')->nullable();
            $table->string('kb_required_permission')->nullable();
            $table->timestamp('kb_last_indexed_at')->nullable();
            $table->string('kb_indexing_status')->nullable();
            $table->integer('kb_chunk_size')->default(800);
            $table->integer('kb_chunk_overlap')->default(100);
            $table->text('kb_content')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'is_kb_indexed',
                'kb_collection',
                'kb_required_permission',
                'kb_last_indexed_at',
                'kb_indexing_status',
                'kb_chunk_size',
                'kb_chunk_overlap',
                'kb_content'
            ]);
        });
    }
};
