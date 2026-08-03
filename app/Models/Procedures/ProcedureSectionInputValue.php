<?php

namespace App\Models\Procedures;

use App\CapturedResult;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureSectionInputValue extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'procedure_worksheet_id',
        'batch_id',
        'captured_result_id',
        'section_key',
        'row_key',
        'column_key',
        'value',
        'uom_id',
        'stock_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'stock_deducted_at' => 'datetime',
        ];
    }

    public function procedureWorksheet(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheet::class);
    }

    public function capturedResult(): BelongsTo
    {
        return $this->belongsTo(CapturedResult::class);
    }

    /**
     * Upsert a shared (non-per-sample) matrix cell value.
     */
    public static function upsertShared(
        string $procedureWorksheetId,
        string $batchId,
        string $sectionKey,
        string $rowKey,
        string $columnKey,
        ?string $value,
        ?string $uomId = null
    ): static {
        return static::updateOrCreate(
            [
                'procedure_worksheet_id' => $procedureWorksheetId,
                'batch_id' => $batchId,
                'captured_result_id' => null,
                'section_key' => $sectionKey,
                'row_key' => $rowKey,
                'column_key' => $columnKey,
            ],
            ['value' => $value, 'uom_id' => $uomId]
        );
    }

    /**
     * Build a keyed map of all shared cell values for a worksheet/batch/section.
     *
     * @return array<string, array<string, array{value: ?string, uom_id: ?string}>>
     *         [row_key => [column_key => {value, uom_id}]]
     */
    public static function sharedMapForSection(
        string $procedureWorksheetId,
        string $batchId,
        string $sectionKey
    ): array {
        $map = [];

        static::query()
            ->where('procedure_worksheet_id', $procedureWorksheetId)
            ->where('batch_id', $batchId)
            ->where('section_key', $sectionKey)
            ->whereNull('captured_result_id')
            ->get()
            ->each(function (self $row) use (&$map) {
                $map[$row->row_key][$row->column_key] = [
                    'value' => $row->value,
                    'uom_id' => $row->uom_id,
                    'id' => $row->id,
                ];
            });

        return $map;
    }
}
