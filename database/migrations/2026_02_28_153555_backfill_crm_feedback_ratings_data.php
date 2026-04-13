<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $metrics = [
            'communication' => 'Communication',
            'turnaround' => 'Turnaround Time',
            'technical' => 'Technical Competence',
            'accuracy' => 'Accuracy of Results',
            'reports' => 'Quality of Reports',
            'professionalism' => 'Professionalism',
            'handling' => 'Handling of Complaints',
            'overall' => 'Overall Rating',
        ];

        $metricIds = [];
        $order = 1;
        foreach ($metrics as $key => $label) {
            $metricId = DB::table('crm_evaluation_metrics')->insertGetId([
                'name' => $label,
                'is_active' => true,
                'display_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $metricIds['rating_' . $key] = $metricId;
        }

        $feedbacks = DB::table('customerfeedbacks')->get();
        
        $inserts = [];
        foreach ($feedbacks as $feedback) {
            foreach ($metricIds as $column => $metricId) {
                if (isset($feedback->$column) && $feedback->$column > 0) {
                    $inserts[] = [
                        'customer_feedback_id' => $feedback->id,
                        'evaluation_metric_id' => $metricId,
                        'rating' => $feedback->$column,
                        'created_at' => $feedback->created_at ?? now(),
                        'updated_at' => $feedback->updated_at ?? now(),
                    ];
                }
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('crm_feedback_ratings')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('crm_feedback_ratings')->truncate();
        DB::table('crm_evaluation_metrics')->truncate();
    }
};
