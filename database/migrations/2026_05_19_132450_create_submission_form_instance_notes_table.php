<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_form_instance_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_form_instance_id');
            $table->text('body');
            $table->enum('visibility', ['internal', 'public'])->default('internal');
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('submission_form_instance_id')
                ->references('id')
                ->on('submission_form_instances')
                ->cascadeOnDelete();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index(['submission_form_instance_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_instance_notes');
    }
};
