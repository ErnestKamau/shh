<?php

namespace App\Services\Sampleworkflow;

use App\Models\TestRequestReportLanguageFile;
use App\Models\TestRequestReportRevision;
use App\SampleHeader;
use Barryvdh\DomPDF\Facade\Pdf;

class TestRequestReportPdfService
{
    public function __construct(
        private readonly TestRequestReportDataService $reportDataService,
    ) {}

    /**
     * @param  array{include_reference_method?: bool}  $options
     * @return array{relative_path: string, online_url: string, filename: string, language: string}
     */
    public function generateAndStore(SampleHeader $batch, int $sequence, string $language, array $options = []): array
    {
        $batch->loadMissing(['customer', 'sample_type', 'samples']);
        $language = $this->normalizeLanguage($language);
        $includeReferenceMethod = (bool) ($options['include_reference_method'] ?? false);

        $jobNumber = $batch->batch_code;
        $reportNumber = $jobNumber.'-R'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);

        $reportData = $this->reportDataService->build($batch, $reportNumber);
        $labels = $this->labelsFor($language);
        $isRTL = $language === 'ar';

        $verificationUrl = route('generateTestRequestReport', [
            'batch_id' => $batch->id,
            'seq' => $sequence,
            'lang' => $language,
            'mode' => 'pdf',
            'include_reference_method' => $includeReferenceMethod ? 1 : 0,
        ]);

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

        $viewData = array_merge($reportData, [
            'language' => $language,
            'labels' => $labels,
            'revisions' => $revisions,
            'isRTL' => $isRTL,
            'isPdfMode' => true,
            'includeReferenceMethod' => $includeReferenceMethod,
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

        $customerName = preg_replace('/[^A-Za-z0-9\-\_]/', '_', (string) ($batch->customer->name ?? 'customer'));
        $customerName = trim($customerName, '_') ?: 'customer';
        $filename = 'TRR_'.$reportNumber.'-'.$language.'.pdf';
        $relativePath = '/reports/'.$customerName.'/'.$filename;
        $absoluteDir = storage_path('app/reports/'.$customerName);

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
                'report_title' => 'تقرير الاختبار المعملي',
                'certificate_no' => 'رقم الشهادة',
                'page_of' => 'صفحة %d من %d',
                'attention' => 'إلى عناية',
                'client' => 'العميل',
                'address' => 'العنوان والموقع',
                'report_no' => 'رقم التقرير',
                'sample_no' => 'رقم العينة',
                'date_received' => 'تاريخ الاستلام',
                'date_reported' => 'تاريخ التقرير',
                'container_type' => 'نوع الحاوية',
                'sample_description' => 'وصف العينة',
                'weight' => 'كمية العينة',
                'sampled_by' => 'أخذ العينة بواسطة',
                'sample_temperature' => 'درجة حرارة العينة',
                'sample_preservation' => 'حفظ العينة',
                'production_date' => 'تاريخ الإنتاج',
                'expiry_date' => 'تاريخ الانتهاء',
                'lot_no' => 'رقم الدُفعة',
                'no_of_pages' => 'عدد الصفحات',
                'date_of_analysis' => 'تاريخ التحليل',
                'packaging' => 'التعبئة',
                'sample_information' => 'معلومات العينة',
                'sample_weight' => 'وزن العينة',
                'ship_name' => 'السفينة / المركب',
                'port_of_loading' => 'ميناء التحميل',
                'port_of_discharge' => 'ميناء التفريغ',
                'seal_number' => 'رقم الختم',
                'sample_reference' => 'مرجع العينة',
                'sample_point' => 'نقطة العينة',
                'condition' => 'الحالة',
                'analyte' => 'معامل الاختبار',
                'results' => 'النتائج',
                'unit' => 'الوحدة',
                'specification' => 'المواصفة',
                'standard_name' => 'اسم المواصفة',
                'reference_method' => 'الطريقة المرجعية',
                'mu_percent' => 'عدم اليقين %',
                'method' => 'طريقة التحليل',
                'no_results' => 'لا توجد نتائج لهذه العينة.',
                'no_samples' => 'لم يتم العثور على عينات لهذه الدفعة.',
                'analysis_conducted' => 'التحليل بواسطة',
                'test_method_dev' => 'انحراف طريقة الاختبار: لا يوجد',
                'signed_behalf' => 'موقّع لصالح',
                'no_signature' => 'لا يوجد توقيع',
                'results_relate' => 'تتعلق نتائج الاختبار بالعينات التي تم اختبارها فقط.',
                'no_reproduce' => 'لا يجوز إعادة إنتاج هذا التقرير إلا كاملاً بإذن كتابي من المختبر.',
                'end_of_text' => 'نهاية النص',
                'issued_on' => 'صدر في',
                'disclaimer' => 'إخلاء المسؤولية: تمت اختبار جميع العينات في مختبر طرف ثالث',
            ],
            'pt' => [
                'report_title' => 'RELATÓRIO DE ENSAIO LABORATORIAL',
                'certificate_no' => 'Certificado n.º',
                'page_of' => 'Página %d de %d',
                'attention' => 'À atenção de',
                'client' => 'Cliente',
                'address' => 'Endereço e Localização',
                'report_no' => 'N.º do Relatório',
                'sample_no' => 'N.º da Amostra',
                'date_received' => 'Data de Receção',
                'date_reported' => 'Data do Relatório',
                'container_type' => 'Tipo de Recipiente',
                'sample_description' => 'Descrição da Amostra',
                'weight' => 'Quantidade da Amostra',
                'sampled_by' => 'Amostrado por',
                'sample_temperature' => 'Temperatura da Amostra',
                'sample_preservation' => 'Preservação da Amostra',
                'production_date' => 'Data de Produção',
                'expiry_date' => 'Data de Validade',
                'lot_no' => 'N.º de Lote',
                'no_of_pages' => 'N.º de Páginas',
                'date_of_analysis' => 'Data da Análise',
                'packaging' => 'Embalagem',
                'sample_information' => 'Informação da amostra',
                'sample_weight' => 'Peso da amostra',
                'ship_name' => 'Navio / embarcação',
                'port_of_loading' => 'Porto de carregamento',
                'port_of_discharge' => 'Porto de descarga',
                'seal_number' => 'Número do selo',
                'sample_reference' => 'Referência da Amostra',
                'sample_point' => 'Ponto de Amostragem',
                'condition' => 'Condição',
                'analyte' => 'Parâmetro de Ensaio',
                'results' => 'Resultados',
                'unit' => 'Unidade',
                'specification' => 'Especificação',
                'standard_name' => 'Nome da Norma',
                'reference_method' => 'Método de Referência',
                'mu_percent' => 'I.M. %',
                'method' => 'Método de análise',
                'no_results' => 'Nenhum resultado registado para esta amostra.',
                'no_samples' => 'Nenhuma amostra encontrada para este lote.',
                'analysis_conducted' => 'Análise conduzida por',
                'test_method_dev' => 'Desvio do método de ensaio: Nenhum',
                'signed_behalf' => 'Assinado por e em nome de',
                'no_signature' => 'Sem assinatura registada',
                'results_relate' => 'Os resultados dos ensaios referem-se apenas às amostras ensaiadas.',
                'no_reproduce' => 'Este relatório não pode ser reproduzido, exceto na íntegra, sem aprovação escrita do Laboratório.',
                'end_of_text' => 'Fim do texto',
                'issued_on' => 'Emitido em',
                'disclaimer' => 'AVISO: TODAS AS AMOSTRAS FORAM ENSAIADAS NUM LABORATÓRIO EXTERNO',
            ],
            default => [
                'report_title' => 'LABORATORY TEST REPORT',
                'certificate_no' => 'Certificate no.',
                'page_of' => 'Page %d of %d',
                'attention' => 'Attention',
                'client' => 'Client',
                'address' => 'Address and Location',
                'report_no' => 'Report No',
                'sample_no' => 'Sample No.',
                'date_received' => 'Date received',
                'date_reported' => 'Date Reported',
                'container_type' => 'Container Type',
                'sample_description' => 'Sample Description',
                'weight' => 'Sample Quantity',
                'sampled_by' => 'Sampled By',
                'sample_temperature' => 'Sample Temperature',
                'sample_preservation' => 'Sample Preservation',
                'production_date' => 'Production Date',
                'expiry_date' => 'Expiry Date',
                'lot_no' => 'Lot No.',
                'no_of_pages' => 'No. of pages',
                'date_of_analysis' => 'Date of Analysis',
                'packaging' => 'Packaging',
                'sample_information' => 'Sample information',
                'sample_weight' => 'Sample weight',
                'ship_name' => 'Ship / vessel',
                'port_of_loading' => 'Port of loading',
                'port_of_discharge' => 'Port of discharge',
                'seal_number' => 'Seal number',
                'sample_reference' => 'Sample Reference',
                'sample_point' => 'Sample Point',
                'condition' => 'Condition',
                'analyte' => 'Test Parameter',
                'results' => 'Results',
                'unit' => 'Unit',
                'specification' => 'Specification',
                'standard_name' => 'Standard Name',
                'reference_method' => 'Reference Method',
                'mu_percent' => 'M.U%',
                'method' => 'Method of Analysis',
                'no_results' => 'No results captured for this sample.',
                'no_samples' => 'No samples found for this batch.',
                'analysis_conducted' => 'Analysis conducted by',
                'test_method_dev' => 'Test method deviation: None',
                'signed_behalf' => 'Signed for and on behalf of',
                'no_signature' => 'No signature on file',
                'results_relate' => 'Test results relate only to the samples tested.',
                'no_reproduce' => 'This report shall not be reproduced except in full, without the written approval of the Laboratory.',
                'end_of_text' => 'End of text',
                'issued_on' => 'Issued on',
                'disclaimer' => 'DISCLAIMER: ALL THE SAMPLES WERE TESTED AT A THIRD-PARTY LABORATORY',
            ],
        };
    }
}
