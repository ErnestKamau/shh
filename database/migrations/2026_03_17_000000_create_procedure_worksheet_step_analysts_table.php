<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_worksheet_step_analysts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('batch_id');
            $table->bigInteger('analyte_id');
            $table->bigInteger('procedure_worksheet_id');
            $table->bigInteger('procedure_worksheet_step_id');
            $table->json('analyst_ids')->nullable();
            $table->timestamps();

            $table->unique([
                'batch_id',
                'analyte_id',
                'procedure_worksheet_id',
                'procedure_worksheet_step_id',
            ], 'pws_step_analysts_unique');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_worksheet_step_analysts');
    }
};

