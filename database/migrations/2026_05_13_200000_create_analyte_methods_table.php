<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyte_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('analyte_id');
            $table->uuid('analysis_method_id');
            $table->timestamps();

            $table->foreign('analyte_id')->references('id')->on('analytes')->onDelete('cascade');
            $table->foreign('analysis_method_id')->references('id')->on('analysis_methods')->onDelete('cascade');
            $table->unique(['analyte_id', 'analysis_method_id']);
        });

        // Migrate existing comma-separated method data to pivot table
        $analytes = DB::table('analytes')
            ->whereNotNull('method')
            ->where('method', '!=', '')
            ->get(['id', 'method']);

        foreach ($analytes as $analyte) {
            $methodIds = array_filter(array_map('trim', explode(',', $analyte->method)));
            foreach ($methodIds as $methodId) {
                if (!empty($methodId)) {
                    // Verify method exists before inserting
                    $exists = DB::table('analysis_methods')->where('id', $methodId)->exists();
                    if ($exists) {
                        DB::table('analyte_methods')->insertOrIgnore([
                            'analyte_id'         => $analyte->id,
                            'analysis_method_id' => $methodId,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analyte_methods');
    }
};
