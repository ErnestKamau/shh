<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyte_equipment', function (Blueprint $table) {
            $table->id();
            $table->uuid('analyte_id');
            $table->uuid('equipment_id');
            $table->timestamps();

            $table->foreign('analyte_id')->references('id')->on('analytes')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->unique(['analyte_id', 'equipment_id']);
        });

        // Migrate existing comma-separated equipment_id data to pivot table
        $analytes = DB::table('analytes')
            ->whereNotNull('equipment_id')
            ->whereRaw("equipment_id::text != ''")
            ->get(['id', 'equipment_id']);

        foreach ($analytes as $analyte) {
            $equipmentIds = array_filter(array_map('trim', explode(',', $analyte->equipment_id)));
            foreach ($equipmentIds as $equipmentId) {
                if (!empty($equipmentId)) {
                    // Verify equipment exists before inserting
                    $exists = DB::table('equipment')->where('id', $equipmentId)->exists();
                    if ($exists) {
                        DB::table('analyte_equipment')->insertOrIgnore([
                            'analyte_id'   => $analyte->id,
                            'equipment_id' => $equipmentId,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analyte_equipment');
    }
};
