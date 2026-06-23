<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_number_sequences')) {
            return;
        }

        Schema::create('job_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->char('date_ymd', 6)->unique();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_number_sequences');
    }
};
