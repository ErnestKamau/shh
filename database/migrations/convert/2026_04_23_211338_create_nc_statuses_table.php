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
        Schema::create('nc_statuses', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_nc_statuses_company_id_6e25e57c');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['company_id'], 'fk_nc_statuses_company_id_fc706f40')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nc_statuses');
    }
};
