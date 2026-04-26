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
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_roles_company_id_bf44f765');
            $table->string('permissions', 8000)->nullable();
            $table->boolean('active')->default(false);
            $table->integer('level')->default(0);
            $table->foreign(['company_id'], 'fk_roles_company_id_ac3f7bb4')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
