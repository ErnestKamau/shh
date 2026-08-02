<?php

namespace Tests\Unit\SubmissionForm;

use App\Models\SubmissionForm;
use App\SampleType;
use App\Services\SubmissionForm\PortalTestRequestFormSampleTypeResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalTestRequestFormSampleTypeResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_untied_export_form_is_not_tied_and_offers_all_active_sample_types(): void
    {
        $food = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);
        SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Inactive',
            'code' => 'OFF',
            'active' => 0,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form For Exportation products',
            'document_code' => 'TRF-',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $resolver = app(PortalTestRequestFormSampleTypeResolver::class);

        $this->assertFalse($resolver->isTiedToSampleTypes($form));
        $this->assertSame([], $resolver->mapForApi($form));

        $options = $resolver->selectableOptionsForApi($form);
        $ids = array_column($options, 'id');

        $this->assertContains((string) $food->id, $ids);
        $this->assertContains((string) $water->id, $ids);
        $this->assertCount(2, $options);
    }

    public function test_tied_water_form_limits_options_to_resolved_types(): void
    {
        $water = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);
        SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form - Water',
            'document_code' => 'TRF-WATER-020',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);
        $form->sampleTypes()->attach($water->id);
        $form->load('sampleTypes');

        $resolver = app(PortalTestRequestFormSampleTypeResolver::class);

        $this->assertTrue($resolver->isTiedToSampleTypes($form));
        $this->assertSame([(string) $water->id], array_column($resolver->mapForApi($form), 'id'));
        $this->assertSame([(string) $water->id], array_column($resolver->selectableOptionsForApi($form), 'id'));
    }

    public function test_food_form_without_pivot_still_resolves_food_sample_types(): void
    {
        $food = SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Food',
            'code' => 'FOOD',
            'active' => 1,
        ]);
        SampleType::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Water',
            'code' => 'WTR',
            'active' => 1,
        ]);

        $form = SubmissionForm::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Request Form - Food',
            'document_code' => 'TRF-FOOD-019',
            'form_type' => 'template',
            'is_active' => true,
            'is_published' => true,
            'is_customer_portal_form' => true,
        ]);

        $resolver = app(PortalTestRequestFormSampleTypeResolver::class);
        $resolved = $resolver->resolveForForm($form);

        $this->assertTrue($resolver->isTiedToSampleTypes($form));
        $this->assertSame([(string) $food->id], $resolved->pluck('id')->map(fn ($id) => (string) $id)->all());
        $this->assertSame((string) $food->id, (string) $resolved->first()?->id);
    }
}
