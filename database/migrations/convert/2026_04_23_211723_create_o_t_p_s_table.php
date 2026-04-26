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
        Schema::create('o_t_p_s', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code');
            $table->uuid('user_id')->index('idx_o_t_p_s_user_id_1a6d5459');
            $table->string('model');
            $table->integer('model_id');
            $table->integer('approved_by')->nullable();
            $table->timestamps();
            $table->foreign(['user_id'], 'fk_o_t_p_s_user_id_4eb23e05')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('o_t_p_s');
    }
};
