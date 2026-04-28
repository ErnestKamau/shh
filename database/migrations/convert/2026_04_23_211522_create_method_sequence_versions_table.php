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
            $table->uuid('id');
            $table->uuid('method_sequence_id')->index('idx_method_sequence_versions_method_sequence_id_9c0992c2');
            $table->integer('version_number')->index('idx_method_sequence_versions_version_number_c9eb3fe6');
            $table->boolean('is_active')->default(false);
            $table->uuid('created_by')->nullable()->index('idx_method_sequence_versions_created_by_a746e3a9');
            $table->uuid('approved_by')->nullable()->index('idx_method_sequence_versions_approved_by_5dccb160');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['method_sequence_id', 'is_active'], 'idx_method_sequence_versions_method_sequence_id_is_act_0d45ab75');
            $table->unique(['method_sequence_id', 'version_number', 'deleted_at'], 'ms_versions_seq_ver_del_unique');

            $table->primary(['id']);

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
