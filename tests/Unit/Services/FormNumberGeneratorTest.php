<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FormNumberGenerator;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormNumberGeneratorTest extends TestCase
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create tables manually to avoid migration setup issues in this environment
        \Illuminate\Support\Facades\Schema::create('submission_forms', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('naming_convention_prefix')->nullable();
            $table->integer('start_submission_number')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('submission_form_instances', function ($table) {
            $table->id();
            $table->foreignId('submission_form_id');
            $table->string('form_number')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }
    /** @test */
    public function it_uses_start_number_for_first_instance()
    {
        $form = SubmissionForm::create([
            'naming_convention_prefix' => 'SF',
            'start_submission_number' => 100
        ]);

        $result = FormNumberGenerator::generate($form);

        $this->assertEquals(100, $result['sequence_no']);
        $this->assertEquals('SF100/' . date('y'), $result['format']);
    }

    /** @test */
    public function it_increments_for_subsequent_instances()
    {
        $form = SubmissionForm::create([
            'naming_convention_prefix' => 'SF',
            'start_submission_number' => 100
        ]);

        // First instance
        SubmissionFormInstance::create([
            'submission_form_id' => $form->id,
            'sequence_number' => 100,
            'form_number' => 'SF100/' . date('y')
        ]);

        $result = FormNumberGenerator::generate($form);

        $this->assertEquals(101, $result['sequence_no']);
        $this->assertEquals('SF101/' . date('y'), $result['format']);
    }

    /** @test */
    public function it_defaults_to_one_if_start_number_is_null()
    {
        $form = SubmissionForm::create([
            'naming_convention_prefix' => 'SF',
            'start_submission_number' => null
        ]);

        $result = FormNumberGenerator::generate($form);

        $this->assertEquals(1, $result['sequence_no']);
        $this->assertEquals('SF001/' . date('y'), $result['format']);
    }

    /** @test */
    public function it_defaults_to_one_if_start_number_is_zero()
    {
        $form = SubmissionForm::create([
            'naming_convention_prefix' => 'SF',
            'start_submission_number' => 0
        ]);

        $result = FormNumberGenerator::generate($form);

        $this->assertEquals(1, $result['sequence_no']);
        $this->assertEquals('SF001/' . date('y'), $result['format']);
    }
}
