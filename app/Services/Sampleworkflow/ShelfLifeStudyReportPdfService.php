<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\SampleHeader;
use Barryvdh\DomPDF\Facade\Pdf;

class ShelfLifeStudyReportPdfService
{
    public function __construct(
        private readonly ShelfLifeStudyReportDataService $reportDataService,
        private readonly AmendmentReportConfigurationService $amendmentReportConfig,
        private readonly TestRequestReportPdfService $testRequestReportPdfService,
    ) {}

    /**
     * @param  array{logoPublicUrlFallback?: bool}  $options
     * @return array{relative_path: string, online_url: string, filename: string, language: string}
     */
    public function generateAndStore(SampleHeader $batch, int $sequence, string $language = 'en', array $options = []): array
    {
        $batch->loadMissing(['customer', 'sample_type', 'samples']);
        $language = $this->testRequestReportPdfService->normalizeLanguage($language);

        $reportNumber = $this->amendmentReportConfig->formatReportNumberForBatch($batch, max(1, $sequence));

        $viewData = $this->buildViewData($batch, $reportNumber, $sequence, $language, array_merge($options, [
            'isPdfMode' => true,
            'logoPublicUrlFallback' => false,
        ]));

        $pdf = Pdf::loadView('layouts.lab.sample-workflow.report-formats.shelf_life_study_report', $viewData);
        $this->configurePdf($pdf);
        app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf);

        $customerName = preg_replace('/[^A-Za-z0-9\-\_]/', '_', (string) ($batch->customer->name ?? 'customer'));
        $customerName = trim((string) $customerName, '_') ?: 'customer';
        $filename = 'SLSR_'.$reportNumber.'-'.$language.'.pdf';
        $relativePath = '/reports/'.$customerName.'/'.$filename;
        // Must live under app/public so /storage/... (public/storage symlink) can serve it.
        $absoluteDir = storage_path('app/public/reports/'.$customerName);

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $pdf->save($absoluteDir.'/'.$filename);

        return [
            'relative_path' => $relativePath,
            'online_url' => url('/storage'.$relativePath),
            'filename' => $filename,
            'language' => $language,
        ];
    }

    /**
     * @param  array{logoPublicUrlFallback?: bool, isPdfMode?: bool, isPreviewMode?: bool, verificationUrl?: string, footerQrCode?: string}  $options
     * @return array<string, mixed>
     */
    public function buildViewData(
        SampleHeader $batch,
        string $reportNumber,
        int $sequence,
        string $language = 'en',
        array $options = [],
    ): array {
        $language = $this->testRequestReportPdfService->normalizeLanguage($language);
        $isRTL = $language === 'ar';
        $labels = $this->labelsFor($language);

        $reportData = $this->reportDataService->build($batch, $reportNumber, [
            'logoPublicUrlFallback' => (bool) ($options['logoPublicUrlFallback'] ?? false),
        ]);

        $capturedResults = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->get();

        $conclusionByCapturedResultId = $this->reportDataService->buildConclusionIndex($capturedResults);

        $verificationUrl = (string) ($options['verificationUrl'] ?? route('generateShelfLifeStudyReport', [
            'batch_id' => $batch->id,
            'seq' => $sequence,
            'lang' => $language,
            'mode' => 'pdf',
        ]));

        $footerQrCode = (string) ($options['footerQrCode'] ?? '');
        if ($footerQrCode === '' && class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $footerQrCode = 'data:image/svg+xml;base64,'.base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(110)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($verificationUrl)
            );
        }

        return array_merge($reportData, [
            'language' => $language,
            'labels' => $labels,
            'isRTL' => $isRTL,
            'isPdfMode' => (bool) ($options['isPdfMode'] ?? false),
            'isPreviewMode' => (bool) ($options['isPreviewMode'] ?? false),
            'footerQrCode' => $footerQrCode,
            'verificationUrl' => $verificationUrl,
            'conclusionByCapturedResultId' => $conclusionByCapturedResultId,
        ]);
    }

    public function configurePdf($pdf): void
    {
        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('enable_php', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('defaultMediaType', 'print');
        $dompdf->set_option('isFontSubsettingEnabled', true);
        $pdf->setPaper('a4', 'portrait');
    }

    /**
     * @return array<string, string>
     */
    public function labelsFor(string $language): array
    {
        $base = $this->testRequestReportPdfService->labelsFor($language);

        $base['report_title'] = match ($language) {
            'ar' => 'تقرير دراسة مدة الصلاحية',
            'pt' => 'RELATÓRIO DE ESTUDO DE PRAZO DE VALIDADE',
            default => 'SHELF LIFE STUDY REPORT',
        };
        $base['manufacturer'] = match ($language) {
            'ar' => 'الشركة المصنعة',
            'pt' => 'Fabricante',
            default => 'Manufacturer',
        };
        $base['declared_shelf_life'] = match ($language) {
            'ar' => 'مدة الصلاحية المعلنة',
            'pt' => 'Prazo de validade declarado',
            default => 'Declared Shelf Life',
        };
        $base['storage_condition'] = match ($language) {
            'ar' => 'ظروف التخزين',
            'pt' => 'Condição de armazenamento',
            default => 'Storage Condition',
        };
        $base['sample_delivered_by'] = match ($language) {
            'ar' => 'تم تسليم العينة بواسطة',
            'pt' => 'Amostra entregue por',
            default => 'Sample Delivered By',
        };
        $base['lot_no'] = match ($language) {
            'ar' => 'رقم دفعة التشغيلة',
            'pt' => 'N.º de Lote do Batch',
            default => 'Batch Lot No.',
        };
        $base['accelerated_conditions'] = match ($language) {
            'ar' => 'ظروف دراسة مدة الصلاحية المعجّلة',
            'pt' => 'Condições do estudo acelerado de prazo de validade',
            default => 'Accelerated Shelf-Life Study Conditions',
        };
        $base['study_type'] = match ($language) {
            'ar' => 'نوع الدراسة',
            'pt' => 'Tipo de estudo',
            default => 'Study Type',
        };
        $base['accelerated_temperature'] = match ($language) {
            'ar' => 'درجة الحرارة المعجّلة',
            'pt' => 'Temperatura acelerada',
            default => 'Accelerated Temperature',
        };
        $base['study_duration'] = match ($language) {
            'ar' => 'مدة الدراسة',
            'pt' => 'Duração do estudo',
            default => 'Study Duration',
        };
        $base['relative_humidity'] = match ($language) {
            'ar' => 'الرطوبة النسبية',
            'pt' => 'Humidade relativa',
            default => 'Relative Humidity',
        };
        $base['evaluation_type'] = match ($language) {
            'ar' => 'نوع التقييم',
            'pt' => 'Tipo de avaliação',
            default => 'Evaluation Type',
        };
        $base['sampling_frequency'] = match ($language) {
            'ar' => 'تكرار أخذ العينات',
            'pt' => 'Frequência de amostragem',
            default => 'Sampling Frequency',
        };
        $base['conclusion'] = match ($language) {
            'ar' => 'الاستنتاج',
            'pt' => 'Conclusão',
            default => 'Conclusion',
        };
        $base['shelf_life_note_1'] = match ($language) {
            'ar' => 'يعتمد تقدير مدة الصلاحية على نتائج دراسة التخزين المعجّل وتقييم معايير الجودة الحرجة.',
            'pt' => 'A estimativa do prazo de validade baseia-se nos resultados do estudo de armazenamento acelerado e na avaliação de parâmetros críticos de qualidade.',
            default => 'Shelf-life estimation is based on accelerated storage study results and evaluation of critical quality parameters.',
        };
        $base['shelf_life_note_2'] = match ($language) {
            'ar' => 'قد تختلف مدة الصلاحية الفعلية حسب التغليف والمناولة والنقل وظروف التخزين الموصى بها.',
            'pt' => 'O prazo de validade real pode variar consoante a embalagem, manuseamento, transporte e condições de armazenamento recomendadas.',
            default => 'The actual shelf life may vary depending on packaging, handling, transportation, and recommended storage conditions.',
        };

        return $base;
    }
}
