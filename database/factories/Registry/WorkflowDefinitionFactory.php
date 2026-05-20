<?php

namespace Database\Factories\Registry;

use App\Models\Registry\WorkflowDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WorkflowDefinition>
 */
class WorkflowDefinitionFactory extends Factory
{
    protected $model = WorkflowDefinition::class;

    public function definition(): array
    {
        $code = 'wf_' . Str::lower(Str::random(8));

        return [
            'name' => fake()->words(3, true),
            'code' => $code,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'company_id' => null,
        ];
    }
}
