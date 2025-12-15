<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_template_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_template_id');
            $table->bigInteger('user_id')->nullable(); // Who submitted it
            $table->json('data'); // snapshot of submitted data
            $table->json('meta')->nullable(); // IP, User Agent, etc.
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_template_submissions');
    }
};
