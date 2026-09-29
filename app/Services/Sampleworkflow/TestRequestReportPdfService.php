<?php

namespace App\Services\Sampleworkflow;

use App\BatchAmmendment;
use App\Models\TestRequestReportLanguageFile;
use App\Models\TestRequestReportRevision;
use App\SampleHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Support\Facades\Log;

class TestRequestReportPdfService
{
    public function __construct(
        private readonly TestRequestReportDataService $reportDataService,
        private readonly AmendmentReportConfigurationService $amendmentReportConfig,
    ) {}

    /**
     * @param  array{
     *     include_reference_method?: bool,
     *     show_specification?: bool,
     *     show_specification_standard?: bool,
     *     show_mu_percent?: bool,
     *     lab_section_id?: string|null
     * }  $options
     * @return array{relative_path: string, online_url: string, filename: string, language: string}
     */
    public function generateAndStore(
        SampleHeader $batch,
        int $sequence,
        string $language,
        array $options = [],
        ?string $userId = null,
    ): array {
        $batch->loadMissing(['customer', 'sample_type', 'samples']);
        $language = $this->normalizeLanguage($language);
        $includeReferenceMethod = (bool) ($options['include_reference_method'] ?? false);
        $showSpecification = (bool) ($options['show_specification'] ?? true);
        $showSpecificationStandard = (bool) ($options['show_specification_standard'] ?? true);
        $showMuPercent = (bool) ($options['show_mu_percent'] ?? true);
        $filterLabSectionId = $this->reportDataService->normalizeLabSectionId($options['lab_section_id'] ?? null);
        $filterLabSectionIds = $this->reportDataService->normalizeLabSectionIds(
            $options['lab_section_ids'] ?? $filterLabSectionId
        );

        $jobNumber = (string) $batch->batch_code;
        $reportNumber = $this->amendmentReportConfig->formatReportNumber($jobNumber, max(1, $sequence));

        $reportData = $this->reportDataService->build($batch, $reportNumber, [
            'lab_section_ids' => $filterLabSectionIds,
            'sample_ids' => $options['sample_ids'] ?? null,
        ]);
        $labels = $this->labelsFor($language);
        $isRTL = $language === 'ar';

        $verificationUrlParams = [
            'batch_id' => $batch->id,
            'seq' => $sequence,
            'lang' => $language,
            'mode' => 'pdf',
            'include_reference_method' => $includeReferenceMethod ? 1 : 0,
            'show_specification' => $showSpecification ? 1 : 0,
            'show_specification_standard' => $showSpecificationStandard ? 1 : 0,
            'show_mu_percent' => $showMuPercent ? 1 : 0,
        ];
        if ($filterLabSectionIds !== []) {
            $verificationUrlParams['lab_section_ids'] = implode(',', $filterLabSectionIds);
        }
        $verificationUrl = route('generateTestRequestReport', $verificationUrlParams);

        $footerQrCode = '';
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $footerQrCode = 'data:image/svg+xml;base64,'.base64_encode(
                \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(110)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($verificationUrl)
            );
        }

        $revisions = TestRequestReportRevision::query()
            ->where('batch_id', $batch->id)
            ->orderByDesc('revision_no')
            ->get();

        $ammendment = BatchAmmendment::resolveForBatch($batch);
        $amendmentVersion = (int) ($ammendment->version_number ?? $batch->is_amendment ?? $sequence);
        // is_amendment defaults to 1 for original jobs; only true amendments (> 1) get -V suffixes.
        if ((int) ($batch->is_amendment ?? 0) > 1) {
            app(JobSampleNumberingService::class)
                ->syncSampleCodeSuffixesForBatch($batch, (int) $batch->is_amendment);
        } else {
            app(JobSampleNumberingService::class)
                ->clearSampleCodeSuffixesForBatch($batch);
        }
        $amendmentDisplay = $this->amendmentDisplayData($labels, $amendmentVersion, $jobNumber);

        $viewData = array_merge($reportData, [
            'language' => $language,
            'labels' => $labels,
            'revisions' => $revisions,
            'ammendment' => $ammendment,
            'amendmentDisplay' => $amendmentDisplay,
            'isRTL' => $isRTL,
            'isPdfMode' => true,
            'includeReferenceMethod' => $includeReferenceMethod,
            'showSpecification' => $showSpecification,
            'showSpecificationStandard' => $showSpecificationStandard,
            'showMuPercent' => $showMuPercent,
            'footerQrCode' => $footerQrCode,
            'verificationUrl' => $verificationUrl,
        ]);

        $pdf = Pdf::loadView('layouts.lab.sample-workflow.report-formats.test_request_report', $viewData);
        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('enable_php', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('defaultMediaType', 'print');
        $dompdf->set_option('isFontSubsettingEnabled', true);
        $pdf->setPaper('a4', 'portrait');
        app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf);

        return $this->persistOfficialPdf(
            $batch,
            $pdf,
            $reportNumber,
            $language,
            $sequence,
            $userId ?? (auth()->id() ? (string) auth()->id() : null),
        );
    }

    /**
     * Write a unique PDF snapshot so regenerating never overwrites a previous file.
     *
     * @return array{relative_path: string, online_url: string, filename: string, language: string}
     */
    public function persistOfficialPdf(
        SampleHeader $batch,
        DomPdfDocument $pdf,
        string $reportNumber,
        string $language,
        int $sequence,
        ?string $userId = null,
        bool $isDraft = false,
    ): array {
        $language = $this->normalizeLanguage($language);
        $customerName = preg_replace('/[^A-Za-z0-9\-\_]/', '_', (string) ($batch->customer->name ?? 'customer'));
        $customerName = trim((string) $customerName, '_') ?: 'customer';
        $filename = $this->uniqueStoredFilename($reportNumber, $language, $isDraft);
        $relativePath = '/reports/'.$customerName.'/'.$filename;
        $absoluteDir = storage_path('app/public/reports/'.$customerName);

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $pdf->save($absoluteDir.'/'.$filename);

        $result = [
            'relative_path' => $relativePath,
            'online_url' => url('/storage'.$relativePath),
            'filename' => $filename,
            'language' => $language,
        ];

        $attachmentService = app(BatchWorkflowDocumentAttachmentService::class);

        try {
            $attachmentService->attachTestReport(
                $batch,
                $result['online_url'],
                $userId,
                $attachmentService->testReportAttachmentTitle($reportNumber, $language, $isDraft),
                $isDraft,
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to attach generated Test Report to batch attachments', [
                'batch_id' => $batch->id,
                'is_draft' => $isDraft,
                'error' => $e->getMessage(),
            ]);
        }

        if (! $isDraft) {
            $this->persistRevisionFile($batch, $sequence, $result, $language);
        }

        return $result;
    }

    /**
     * @param  list<string>  $languages
     * @return list<TestRequestReportLanguageFile>
     */
    public function generatePortalLanguageFiles(SampleHeader $batch, int $revisionNo, array $languages): array
    {
        $stored = [];

        foreach ($languages as $language) {
            $normalized = $this->normalizeLanguage($language);
            $generated = $this->generateAndStore($batch, $revisionNo, $normalized);

            $stored[] = TestRequestReportLanguageFile::query()->updateOrCreate(
                [
                    'batch_id' => $batch->id,
                    'revision_no' => $revisionNo,
                    'language' => $normalized,
                ],
                [
                    'report_url' => $generated['relative_path'],
                    'report_online_url' => $generated['online_url'],
                ]
            );
        }

        return $stored;
    }

    public function normalizeLanguage(string $language): string
    {
        return in_array($language, ['en', 'ar', 'pt'], true) ? $language : 'en';
    }

    /**
     * @return array<string, string>
     */
    public function labelsFor(string $language): array
    {
        $language = $this->normalizeLanguage($language);

        return match ($language) {
            'ar' => [
                'report_title' => 'تقرير الاختبار',
                'draft_report_title' => 'مسودة تقرير الاختبار',
                'certificate_no' => 'رقم الشهادة',
                'page_of' => 'صفحة %d من %d',
                'attention' => 'إلى عناية',
                'client' => 'العميل',
                'address' => 'العنوان والموقع',
                'report_no' => 'رقم التقرير',
                'job_no' => 'رقم المهمة',
                'sample_no' => 'رقم العينة',
                'date_received' => 'تاريخ الاستلام',
                'date_reported' => 'تاريخ التقرير',
                'reporting_date' => 'تاريخ إصدار التقرير',
                'container_type' => 'الحاوية / التعبئة',
                'sample_description' => 'وصف العينة',
                'weight' => 'كمية العينة',
                'sampled_by' => 'أخذ العينة بواسطة',
                'sample_temperature' => 'درجة حرارة العينة',
                'production_date' => 'تاريخ الإنتاج',
                'expiry_date' => 'تاريخ الانتهاء',
                'lot_no' => 'رقم الدُفعة',
                'no_of_pages' => 'عدد الصفحات',
                'date_of_analysis' => 'تاريخ التحليل',
                'analysis_start_date' => 'تاريخ بدء التحليل',
                'analysis_end_date' => 'تاريخ انتهاء التحليل',
                'packaging' => 'التعبئة',
                'sample_information' => 'معلومات العينة',
                'sample_weight' => 'وزن العينة',
                'ship_name' => 'السفينة / المركب',
                'port_of_loading' => 'ميناء التحميل',
                'port_of_discharge' => 'ميناء التفريغ',
                'seal_number' => 'رقم الختم',
                'sample_reference' => 'مرجع العينة',
                'sample_point' => 'نقطة العينة',
                'sampling_location' => 'موقع أخذ العينة',
                'sample_type' => 'نوع العينة',
                'sample_condition' => 'حالة العينة',
                'origin_country' => 'بلد المنشأ',
                'transport_condition' => 'ظروف النقل',
                'sampling_method' => 'طريقة أخذ العينة',
                'additional_notes' => 'ملاحظات إضافية',
                'sample_photo' => 'صورة العينة',
                'condition' => 'الحالة',
                'analyte' => 'الاختبار',
                'lab_section' => 'قسم المختبر',
                'results' => 'النتائج',
                'unit' => 'الوحدة',
                'loq' => 'LOQ',
                'specification' => 'حد المواصفة',
                'standard_name' => 'معيار المواصفة',
                'reference_method' => 'الطريقة المرجعية',
                'mu_percent' => 'عدم اليقين %',
                'method' => 'طريقة التحليل',
                'no_results' => 'لا توجد نتائج لهذه العينة.',
                'no_samples' => 'لم يتم العثور على عينات لهذه الدفعة.',
                'analysis_conducted' => 'التحليل بواسطة',
                'employee_id' => 'رقم الموظف',
                'signed_org' => 'AMSPEC',
                'notes' => 'ملاحظات',
                'test_method_dev' => 'انحراف طريقة الاختبار: لا يوجد',
                'signed_behalf' => 'موقّع لصالح',
                'no_signature' => 'لا يوجد توقيع',
                'results_relate' => 'تتعلق نتائج الاختبار بالعينات التي تم اختبارها فقط.',
                'no_reproduce' => 'لا يجوز إعادة إنتاج هذا التقرير إلا كاملاً بإذن كتابي من المختبر.',
                'lab_address_closing' => 'أُجريت التحاليل في عنوان المختبر الموضح في الترويسة.',
                'end_of_text' => 'نهاية النص',
                'issued_on' => 'صدر في',
                'disclaimer' => 'إخلاء المسؤولية: تمت اختبار جميع العينات في مختبر طرف ثالث',
                'supersedes_original' => 'هذا التقرير يحل محل التقرير الأصلي',
                'amendment_reason' => 'سبب التعديل',
                'amendment_revision' => 'رقم المراجعة',
            ],
            'pt' => [
                'report_title' => 'RELATÓRIO DE ENSAIO',
                'draft_report_title' => 'RASCUNHO DO RELATÓRIO DE ENSAIO',
                'certificate_no' => 'Certificado n.º',
                'page_of' => 'Página %d de %d',
                'attention' => 'À atenção de',
                'client' => 'Cliente',
                'address' => 'Endereço e Localização',
                'report_no' => 'N.º do Relatório',
                'job_no' => 'N.º do Trabalho',
                'sample_no' => 'N.º da Amostra',
                'date_received' => 'Data de Receção',
                'date_reported' => 'Data do Relatório',
                'reporting_date' => 'Data do Relatório',
                'container_type' => 'Recipiente / Embalagem',
                'sample_description' => 'Descrição da Amostra',
                'weight' => 'Quantidade da Amostra',
                'sampled_by' => 'Amostrado por',
                'sample_temperature' => 'Temperatura da Amostra',
                'production_date' => 'Data de Produção',
                'expiry_date' => 'Data de Validade',
                'lot_no' => 'N.º de Lote',
                'no_of_pages' => 'N.º de Páginas',
                'date_of_analysis' => 'Data da Análise',
                'analysis_start_date' => 'Data de início da análise',
                'analysis_end_date' => 'Data de fim da análise',
                'packaging' => 'Embalagem',
                'sample_information' => 'Informação da amostra',
                'sample_weight' => 'Peso da amostra',
                'ship_name' => 'Navio / embarcação',
                'port_of_loading' => 'Porto de carregamento',
                'port_of_discharge' => 'Porto de descarga',
                'seal_number' => 'Número do selo',
                'sample_reference' => 'Referência da Amostra',
                'sample_point' => 'Ponto de Amostragem',
                'sampling_location' => 'Local de amostragem',
                'sample_type' => 'Tipo de amostra',
                'sample_condition' => 'Condição da amostra',
                'origin_country' => 'País de origem',
                'transport_condition' => 'Condição de transporte',
                'sampling_method' => 'Método de amostragem',
                'additional_notes' => 'Notas adicionais',
                'sample_photo' => 'Foto da amostra',
                'condition' => 'Condição',
                'analyte' => 'Ensaio',
                'lab_section' => 'Secção do Laboratório',
                'results' => 'Resultados',
                'unit' => 'Unidade',
                'loq' => 'LOQ',
                'specification' => 'Limite de especificação',
                'standard_name' => 'Norma de especificação',
                'reference_method' => 'Método de Referência',
                'mu_percent' => 'I.M. %',
                'method' => 'Método de análise',
                'no_results' => 'Nenhum resultado registado para esta amostra.',
                'no_samples' => 'Nenhuma amostra encontrada para este lote.',
                'analysis_conducted' => 'Análise conduzida por',
                'employee_id' => 'ID do funcionário',
                'signed_org' => 'AMSPEC',
                'notes' => 'Notas',
                'test_method_dev' => 'Desvio do método de ensaio: Nenhum',
                'signed_behalf' => 'Assinado por e em nome de',
                'no_signature' => 'Sem assinatura registada',
                'results_relate' => 'Os resultados dos ensaios referem-se apenas às amostras ensaiadas.',
                'no_reproduce' => 'Este relatório não pode ser reproduzido, exceto na íntegra, sem aprovação escrita do Laboratório.',
                'lab_address_closing' => 'As análises foram realizadas no endereço do laboratório identificado no cabeçalho.',
                'end_of_text' => 'Fim do texto',
                'issued_on' => 'Emitido em',
                'disclaimer' => 'AVISO: TODAS AS AMOSTRAS FORAM ENSAIADAS NUM LABORATÓRIO EXTERNO',
                'supersedes_original' => 'Este relatório substitui o relatório original',
                'amendment_reason' => 'Motivo da emenda',
                'amendment_revision' => 'N.º da revisão',
            ],
            default => [
                'report_title' => 'TEST REPORT',
                'draft_report_title' => 'DRAFT TEST REPORT',
                'certificate_no' => 'Certificate no.',
                'page_of' => 'Page %d of %d',
                'attention' => 'Attention',
                'client' => 'Client',
                'address' => 'Address and Location',
                'report_no' => 'Report No',
                'job_no' => 'Job No.',
                'sample_no' => 'Sample No.',
                'date_received' => 'Date received',
                'date_reported' => 'Date Reported',
                'reporting_date' => 'Reporting Date',
                'container_type' => 'Container/Packaging',
                'sample_description' => 'Sample Description',
                'weight' => 'Sample Quantity',
                'sampled_by' => 'Sampled By',
                'sample_temperature' => 'Sample Temperature',
                'production_date' => 'Production Date',
                'expiry_date' => 'Expiry Date',
                'lot_no' => 'Lot No.',
                'no_of_pages' => 'No. of pages',
                'date_of_analysis' => 'Date of Analysis',
                'analysis_start_date' => 'Analysis Start date',
                'analysis_end_date' => 'Analysis End date',
                'packaging' => 'Packaging',
                'sample_information' => 'Sample information',
                'sample_weight' => 'Sample weight',
                'ship_name' => 'Ship / vessel',
                'port_of_loading' => 'Port of loading',
                'port_of_discharge' => 'Port of discharge',
                'seal_number' => 'Seal number',
                'sample_reference' => 'Sample Reference',
                'sample_point' => 'Sampling Point',
                'sampling_location' => 'Sampling Location',
                'sample_type' => 'Sample Type',
                'sample_condition' => 'Sample Condition',
                'origin_country' => 'Origin country',
                'transport_condition' => 'Transport Condition',
                'sampling_method' => 'Sampling Method',
                'additional_notes' => 'Additional Notes',
                'sample_photo' => 'Sample Photo',
                'condition' => 'Condition',
                'analyte' => 'Test',
                'lab_section' => 'Lab Section',
                'results' => 'Results',
                'unit' => 'Unit',
                'loq' => 'LOQ',
                'specification' => 'Specification limit',
                'standard_name' => 'Specification Standard',
                'reference_method' => 'Reference Method',
                'mu_percent' => 'M.U%',
                'method' => 'Method of Analysis',
                'no_results' => 'No results captured for this sample.',
                'no_samples' => 'No samples found for this batch.',
                'analysis_conducted' => 'Analysis conducted by',
                'employee_id' => 'Employee ID',
                'signed_org' => 'AMSPEC',
                'notes' => 'Notes',
                'test_method_dev' => 'Test method deviation: None',
                'signed_behalf' => 'Signed for and on behalf of',
                'no_signature' => 'No signature on file',
                'results_relate' => 'Test results relate only to the samples tested.',
                'no_reproduce' => 'This report shall not be reproduced except in full, without the written approval of the Laboratory.',
                'lab_address_closing' => 'The analyses were performed at the laboratory address identified in the header.',
                'end_of_text' => 'End of text',
                'issued_on' => 'Issued on',
                'disclaimer' => 'DISCLAIMER: ALL THE SAMPLES WERE TESTED AT A THIRD-PARTY LABORATORY',
                'supersedes_original' => 'This report supersedes the original report',
                'amendment_reason' => 'Amendment Reason',
                'amendment_revision' => 'Revision No.',
            ],
        };
    }

    /**
     * @param  array<string, string>  $labels
     * @return array{
     *     revisionLabel: string,
     *     reasonLabel: string,
     *     supersedesText: string,
     *     formattedRevision: string,
     *     formattedReportNumber: string,
     *     sampleNumberSuffix: string
     * }
     */
    public function amendmentDisplayData(array $labels, int $version, ?string $jobNumber = null): array
    {
        $config = $this->amendmentReportConfig->amendmentViewData($version, $jobNumber);

        return [
            'revisionLabel' => $config['revision_label'] !== ''
                ? $config['revision_label']
                : (string) ($labels['amendment_revision'] ?? 'Revision No.'),
            'reasonLabel' => $config['reason_label'] !== ''
                ? $config['reason_label']
                : (string) ($labels['amendment_reason'] ?? 'Amendment Reason'),
            'supersedesText' => $config['supersedes_text'] !== ''
                ? $config['supersedes_text']
                : (string) ($labels['supersedes_original'] ?? 'This report supersedes the original report'),
            'formattedRevision' => $config['formatted_revision'],
            'formattedReportNumber' => $config['formatted_report_number'],
            'sampleNumberSuffix' => $config['sample_number_suffix'],
        ];
    }

    private function uniqueStoredFilename(string $reportNumber, string $language, bool $isDraft = false): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', $reportNumber) ?: 'report';
        $stamp = now()->format('YmdHis');
        $suffix = bin2hex(random_bytes(3));
        $prefix = $isDraft ? 'TRR_DRAFT_' : 'TRR_';

        return $prefix.$safeNumber.'-'.$language.'-'.$stamp.'-'.$suffix.'.pdf';
    }

    /**
     * @param  array{relative_path: string, online_url: string, filename: string, language: string}  $stored
     */
    private function persistRevisionFile(
        SampleHeader $batch,
        int $sequence,
        array $stored,
        string $language,
    ): void {
        try {
            $revision = TestRequestReportRevision::query()
                ->where('batch_id', $batch->id)
                ->where('revision_no', $sequence)
                ->latest('id')
                ->first();

            if ($revision === null) {
                return;
            }

            if (
                filled($revision->report_url)
                && (string) $revision->language !== $language
            ) {
                return;
            }

            $revision->report_url = $stored['relative_path'];
            $revision->report_online_url = $stored['online_url'];
            $revision->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to persist Test Report revision file path', [
                'batch_id' => $batch->id,
                'revision_no' => $sequence,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
