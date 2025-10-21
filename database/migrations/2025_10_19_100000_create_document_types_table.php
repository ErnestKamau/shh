<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->string('numbering_format', 200)->default('{TYPE_CODE}-{YEAR}-{SEQ}');
            $table->integer('amendment_limit')->default(5);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('code');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
}

