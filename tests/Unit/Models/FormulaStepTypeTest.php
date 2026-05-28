<?php

namespace Tests\Unit\Models;

use App\Models\Formulars\FormulaStep;
use PHPUnit\Framework\TestCase;

class FormulaStepTypeTest extends TestCase
{
    public function test_static_text_step_detection(): void
    {
        $step = new FormulaStep([
            'step_type' => 'static_text',
            'step_config' => ['content' => 'Hello'],
        ]);

        $this->assertTrue($step->isStaticText());
        $this->assertFalse($step->isCalculable());
        $this->assertSame('Hello', $step->staticTextContent());
    }

    public function test_checkbox_step_detection(): void
    {
        $step = new FormulaStep([
            'step_type' => 'checkbox',
            'step_config' => [
                'options_mode' => 'static',
                'static_options' => ['A', 'B'],
            ],
        ]);

        $this->assertTrue($step->isCheckbox());
        $this->assertFalse($step->isCalculable());
    }

    public function test_custom_table_step_detection(): void
    {
        $step = new FormulaStep([
            'step_type' => 'custom_table',
            'table_mode' => 'dynamic',
        ]);

        $this->assertTrue($step->isCustomTable());
        $this->assertFalse($step->isCalculable());
    }
}
