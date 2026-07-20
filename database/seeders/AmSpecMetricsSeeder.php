<?php

namespace Database\Seeders;

use App\Models\CRM\EvaluationMetric;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmSpecMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deactivate all existing metrics first to avoid clutter
        EvaluationMetric::query()->update(['is_active' => false]);

        $metrics = [
            [
                'name' => 'Answer your emails/calls',
                'prompt_text' => 'Responsiveness and availability of our team to your communication requests.',
            ],
            [
                'name' => 'Clarity of the proposals',
                'prompt_text' => 'Clarity, accuracy, and detail provided in our pricing and project proposals.',
            ],
            [
                'name' => 'Communication and coordination',
                'prompt_text' => 'Our general level of active communication and project coordination with you.',
            ],
            [
                'name' => 'Effectiveness of communication and coordination',
                'prompt_text' => 'The efficiency and overall results of our project communication and scheduling.',
            ],
            [
                'name' => 'Technical team competency & professionalism',
                'prompt_text' => 'The technical skill, competency, and professional behavior demonstrated by our personnel.',
            ],
            [
                'name' => 'Accuracy & unambiguity of reports',
                'prompt_text' => 'The high precision, accuracy, and clear presentation of our laboratory reports.',
            ],
            [
                'name' => 'Timeliness delivery of reports',
                'prompt_text' => 'Meeting agreed timelines and deadlines for the final release and delivery of laboratory reports.',
            ],
            [
                'name' => 'Health, Safety feedback for our staff at your site',
                'prompt_text' => 'The strict observance and positive compliance of our visiting staff with your on-site health and safety regulations.',
            ],
            [
                'name' => 'Timeliness & accuracy of Invoices',
                'prompt_text' => 'Correctness and timeliness in billing and delivery of project invoices.',
            ],
            [
                'name' => 'Dispute/Query handling professionalism',
                'prompt_text' => 'Our professionalism, responsiveness, and fairness when addressing queries, feedback, or complaints.',
            ],
        ];

        foreach ($metrics as $index => $metric) {
            $metricModel = EvaluationMetric::query()->updateOrCreate(
                ['name' => $metric['name']],
                [
                    'prompt_text' => $metric['prompt_text'],
                    'max_rating' => 10,
                    'rating_labels' => [
                        0 => '0',
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                        8 => '8',
                        9 => '9',
                        10 => '10',
                    ],
                    'is_active' => true,
                    'display_order' => $index + 1,
                ]
            );

            if ($metricModel->wasRecentlyCreated) {
                $metricModel->id = (string) Str::uuid();
                $metricModel->save();
            }
        }
    }
}
