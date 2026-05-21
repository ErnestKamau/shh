<?php

namespace Database\Factories\Registry;

use App\Models\Registry\RegistryRequestCategory;
use App\Models\Registry\WorkflowDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RegistryRequestCategory>
 */
class RegistryRequestCategoryFactory extends Factory
{
    protected $model = RegistryRequestCategory::class;

    public function definition(): array
    {
        $code = Str::lower(Str::random(6));

        return [
            'name' => fake()->words(2, true),
            'code' => $code,
            'workflow_definition_id' => WorkflowDefinition::factory(),
            'default_priority' => 'normal',
            'metadata_schema' => null,
            'is_active' => true,
            'company_id' => null,
        ];
    }
}
