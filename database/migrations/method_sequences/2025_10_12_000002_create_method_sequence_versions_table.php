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
        Schema::create('method_sequence_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('method_sequence_id');
            $table->integer('version_number');
            $table->boolean('is_active')->default(false);
            $table->bigInteger('created_by');
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['method_sequence_id', 'is_active']);
            $table->index(['version_number']);
            $table->unique(['method_sequence_id', 'version_number', 'deleted_at'], 'ms_versions_seq_ver_del_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_sequence_versions');
    }
};

