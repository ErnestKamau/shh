<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormService;
use Mockery;
use Tests\TestCase;

class AcceptanceFormServiceJobDispatchTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_customer_signature_moves_to_awaiting_manager_without_batch(): void
    {
        $form = Mockery::mock(AnalysisAcceptanceForm::class)->makePartial();
        $form->status = AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN;
        $form->sample_header_id = null;

        $form->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return ($payload['status'] ?? '') === AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN;
            }))
            ->andReturnTrue();

        $form->shouldReceive('fresh')
            ->once()
            ->with(['lines'])
            ->andReturnSelf();

        $updated = app(AcceptanceFormService::class)->recordCustomerSignature($form, 'Signer', 'sig-data');

        $this->assertSame(AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN, $updated->status);
        $this->assertNull($updated->sample_header_id);
    }
}
