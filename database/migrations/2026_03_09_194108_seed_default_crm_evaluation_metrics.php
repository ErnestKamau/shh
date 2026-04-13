<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed default evaluation metrics so insights and feedback form work.
     */
    public function up(): void
    {
        if (! Schema::hasTable('crm_evaluation_metrics')) {
            return;
        }

        if (DB::table('crm_evaluation_metrics')->exists()) {
            return;
        }

        $metrics = [
            ['name' => 'Communication', 'max_rating' => 4],
            ['name' => 'Turnaround Time', 'max_rating' => 4],
            ['name' => 'Technical Competence', 'max_rating' => 4],
            ['name' => 'Accuracy of Results', 'max_rating' => 4],
            ['name' => 'Quality of Reports', 'max_rating' => 4],
            ['name' => 'Professionalism', 'max_rating' => 4],
            ['name' => 'Handling of Complaints', 'max_rating' => 4],
            ['name' => 'Overall Rating', 'max_rating' => 4],
        ];

        $hasMaxRating = Schema::hasColumn('crm_evaluation_metrics', 'max_rating');
        $now = now();
        $order = 1;
        foreach ($metrics as $m) {
            $row = [
                'name' => $m['name'],
                'is_active' => true,
                'display_order' => $order++,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasMaxRating) {
                $row['max_rating'] = $m['max_rating'];
            }
            DB::table('crm_evaluation_metrics')->insert($row);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('crm_evaluation_metrics')) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('crm_evaluation_metrics')->truncate();
        Schema::enableForeignKeyConstraints();
    }
};
