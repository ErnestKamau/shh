<?php

namespace Database\Factories;

use App\Models\TestReportDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TestReportDocument>
 */
class TestReportDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(12),
            'batch_id' => (string) Str::uuid(),
            'sample_detail_id' => (string) Str::uuid(),
            'revision_no' => 1,
            'language' => 'en',
            'report_number' => fake()->numerify('26090####-001-R01'),
            'file_path' => null,
            'is_official' => true,
            'generated_at' => now(),
        ];
    }

    public function withFile(string $relativePath = 'reports/customer/samples/TRR_sample.pdf'): static
    {
        return $this->state(fn (): array => ['file_path' => $relativePath]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_official' => false]);
    }
}
