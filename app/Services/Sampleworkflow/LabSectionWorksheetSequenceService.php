<?php

namespace App\Services\Sampleworkflow;

use App\SampleAnalysisStage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class LabSectionWorksheetSequenceService
{
    public function previewNextNumber(string $sectionCode, ?Carbon $issuedAt = null): string
    {
        $issuedAt ??= now();
        $sectionCode = $this->normalizeSectionCode($sectionCode);
        $year = (int) $issuedAt->format('Y');
        $datePrefix = $issuedAt->format('ymd');

        $lastSequence = (int) DB::table('lab_section_worksheet_sequences')
            ->where('calendar_year', $year)
            ->whereIn('lab_section_id', function ($query) use ($sectionCode): void {
                $query->select('id')
                    ->from('sample_analysis_stages')
                    ->where('code', $sectionCode);
            })
            ->value('last_sequence');

        return sprintf('%s-%s.%03d', $datePrefix, $sectionCode, $lastSequence + 1);
    }

    public function nextNumber(SampleAnalysisStage $section, ?Carbon $issuedAt = null): array
    {
        $issuedAt ??= now();
        $sectionCode = $this->normalizeSectionCode((string) ($section->code ?? ''));
        if ($sectionCode === '') {
            $sectionCode = 'SEC';
        }

        $year = (int) $issuedAt->format('Y');
        $datePrefix = $issuedAt->format('ymd');

        return DB::transaction(function () use ($section, $year, $datePrefix, $sectionCode): array {
            $row = DB::table('lab_section_worksheet_sequences')
                ->where('lab_section_id', (string) $section->id)
                ->where('calendar_year', $year)
                ->lockForUpdate()
                ->first();

            $sequence = (int) ($row->last_sequence ?? 0) + 1;

            if ($row === null) {
                DB::table('lab_section_worksheet_sequences')->insert([
                    'id' => (string) Str::uuid(),
                    'lab_section_id' => (string) $section->id,
                    'calendar_year' => $year,
                    'last_sequence' => $sequence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('lab_section_worksheet_sequences')
                    ->where('id', (string) $row->id)
                    ->update([
                        'last_sequence' => $sequence,
                        'updated_at' => now(),
                    ]);
            }

            return [
                'worksheet_number' => sprintf('%s-%s.%03d', $datePrefix, $sectionCode, $sequence),
                'calendar_year' => $year,
                'sequence' => $sequence,
                'section_code' => $sectionCode,
            ];
        });
    }

    private function normalizeSectionCode(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/[^A-Z0-9]+/', '', $code) ?? '';

        return $code !== '' ? $code : 'SEC';
    }
}
