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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->default('form');
            $table->string('status')->default('draft'); // draft, published, archived
            $table->json('header_content')->nullable(); // Static header data
            $table->json('footer_content')->nullable(); // Static footer data
            $table->uuid('created_by')->nullable()->index();
            $table->string('process_type')->nullable();
            $table->unsignedBigInteger('process_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['process_type', 'process_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};
