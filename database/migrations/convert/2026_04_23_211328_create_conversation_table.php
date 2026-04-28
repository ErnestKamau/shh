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
        Schema::create('conversation', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('from_user_id')->nullable();
            $table->integer('to_user_id')->nullable();
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_conversation_company_id_211745e1');
            $table->smallInteger('active')->default(1);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation');
    }
};
