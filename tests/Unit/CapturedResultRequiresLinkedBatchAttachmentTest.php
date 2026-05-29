<?php

namespace Tests\Unit;

use App\CapturedResult;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CapturedResultRequiresLinkedBatchAttachmentTest extends TestCase
{
    #[DataProvider('requiresLinkedBatchAttachmentProvider')]
    public function test_requires_linked_batch_attachment(
        bool $hasProcedureWorksheet,
        ?string $batchAttachmentId,
        ?string $result,
        bool $expected,
    ): void {
        $captured = new CapturedResult([
            'has_procedure_worksheet' => $hasProcedureWorksheet,
            'batch_attachment_id' => $batchAttachmentId,
            'result' => $result,
        ]);

        $this->assertSame($expected, $captured->requiresLinkedBatchAttachment());
    }

    public static function requiresLinkedBatchAttachmentProvider(): array
    {
        return [
            'non procedure worksheet' => [false, null, 'No attachment', false],
            'placeholder without attachment' => [true, null, 'No attachment', true],
            'has attachment placeholder' => [true, null, 'has attachment', true],
            'substantive worksheet result' => [true, null, 'Negative', false],
            'substantive positive result' => [true, null, 'Positive', false],
            'already linked as attached text' => [true, null, 'as attached', false],
            'linked attachment id present' => [true, '00000000-0000-4000-8000-000000000001', 'No attachment', false],
            'empty result still pending' => [true, null, '', true],
            'null result still pending' => [true, null, null, true],
        ];
    }
}
