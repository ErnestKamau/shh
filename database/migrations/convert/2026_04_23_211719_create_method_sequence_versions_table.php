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
            $table->uuid('method_sequence_id')->index('idx_method_sequence_versions_method_sequence_id_ca297470');
            $table->integer('version_number')->index('idx_method_sequence_versions_version_number_f14ee1d9');
            $table->boolean('is_active')->default(false);
            $table->bigInteger('created_by');
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['method_sequence_id', 'is_active'], 'idx_method_sequence_versions_method_sequence_id_is_act_c6b2491a');
            $table->unique(['method_sequence_id', 'version_number', 'deleted_at'], 'ms_versions_seq_ver_del_unique');
            $table->foreign(['method_sequence_id'], 'fk_method_sequence_versions_method_sequence_id_40a73fea')->references(['id'])->on('method_sequences')->onUpdate('no action')->onDelete('cascade');

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
