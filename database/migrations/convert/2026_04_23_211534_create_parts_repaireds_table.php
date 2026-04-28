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
        Schema::create('parts_repaireds', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('log_id');
            $table->text('material_parts')->nullable();
            $table->text('tools_equipment')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->string('quantity', 100)->nullable();
            $table->uuid('user_id')->nullable()->index('idx_parts_repaireds_user_id_22e83190');
            $table->text('name')->nullable();
            $table->text('comments')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts_repaireds');
    }
};
