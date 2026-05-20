<?php

namespace Database\Factories\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistryRequest>
 */
class RegistryRequestFactory extends Factory
{
    protected $model = RegistryRequest::class;

    public function definition(): array
    {
        return [
            'reference_no' => 'REG-' . now()->format('Y') . '-' . fake()->unique()->numerify('#####'),
            'request_category_id' => RegistryRequestCategory::factory(),
            'workflow_definition_id' => null,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'entity_type' => null,
            'entity_id' => null,
            'metadata' => [],
            'priority' => 'normal',
            'direction' => 'incoming',
            'current_stage' => null,
            'status' => RegistryRequest::STATUS_DRAFT,
            'submitting_party' => fake()->company(),
            'submitted_by' => null,
            'assigned_to' => null,
            'received_by' => null,
            'received_at' => now(),
            'closed_at' => null,
            'company_id' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => RegistryRequest::STATUS_OPEN,
            'current_stage' => 'registry',
        ]);
    }
}
