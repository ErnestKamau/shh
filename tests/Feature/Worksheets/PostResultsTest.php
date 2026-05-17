<?php

namespace Tests\Feature\Worksheets;

use App\Models\SampleCapturedTestStagesTrack;
use App\SampleHeader;
use App\User;
use Tests\TestCase;

class PostResultsTest extends TestCase
{
    public function test_post_method_sequence_results_requires_end_stage(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No users in database.');
        }

        $track = SampleCapturedTestStagesTrack::query()
            ->whereHas('testStage', fn ($q) => $q->where('is_result_stage', true)->where('is_end_stage', false))
            ->first();

        if (! $track) {
            $this->markTestSkipped('No intermediate result-stage track available for assertion.');
        }

        $this->actingAs($user);

        $response = $this->postJson(route('method-sequences.post-results'), [
            'batch_id' => $track->capturedResult?->sample_header_id,
            'start_analysis_date' => now()->toDateString(),
            'end_analysis_date' => now()->toDateString(),
            'tracking_data' => [
                ['track_id' => $track->id],
            ],
        ]);

        $response->assertStatus(500);
    }

    public function test_get_method_sequence_tracking_results_filters_end_stage(): void
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No users in database.');
        }

        $batch = SampleHeader::query()->where('isactive', true)->first();
        if (! $batch) {
            $this->markTestSkipped('No active sample headers in database.');
        }

        $this->actingAs($user);

        $response = $this->getJson(route('method-sequences.tracking-results', ['batch' => $batch->id]));

        $response->assertOk();

        $records = $response->json('records') ?? [];
        foreach ($records as $record) {
            $stage = $record['test_stage'] ?? null;
            if (is_array($stage)) {
                $this->assertTrue((bool) ($stage['is_result_stage'] ?? false));
                $this->assertTrue((bool) ($stage['is_end_stage'] ?? false));
            }
        }
    }
}
