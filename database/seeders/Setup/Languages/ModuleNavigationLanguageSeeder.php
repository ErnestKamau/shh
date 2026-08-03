<?php

namespace Database\Seeders\Setup\Languages;

use App\Models\System\TranslationLanguageLine;
use Illuminate\Database\Seeder;

class ModuleNavigationLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'lab' => [
                'module_name' => [
                    'en' => 'Lab Management',
                    'sw' => 'Usimamizi wa Maabara',
                    'pt' => 'Gestão de Laboratório',
                    'ar' => 'إدارة المختبر',
                ],
                'dashboard' => [
                    'en' => 'Dashboard',
                    'sw' => 'Dashibodi',
                    'pt' => 'Painel',
                    'ar' => 'لوحة التحكم',
                ],
                'receive_request' => [
                    'en' => 'Receive request',
                    'sw' => 'Pokea ombi',
                    'pt' => 'Receber solicitação',
                    'ar' => 'استلام الطلب',
                ],
                'request_for_testing' => [
                    'en' => 'Request For Testing',
                    'sw' => 'Ombi la Upimaji',
                    'pt' => 'Pedido de Ensaio',
                    'ar' => 'طلب الاختبار',
                ],
                'sample_workflow' => [
                    'en' => 'Sample Workflow',
                    'sw' => 'Mtiririko wa Sampuli',
                    'pt' => 'Fluxo de Amostras',
                    'ar' => 'سير عمل العينات',
                ],
                'workflow_kpis' => [
                    'en' => 'Workflow KPIs',
                    'sw' => 'Vipimo vya Mtiririko wa Kazi',
                    'pt' => 'KPIs do Fluxo de Trabalho',
                    'ar' => 'مؤشرات سير العمل',
                ],
                'personal_dashboard' => [
                    'en' => 'Personal Dashboard',
                    'sw' => 'Dashibodi ya Kibinafsi',
                    'pt' => 'Painel Pessoal',
                    'ar' => 'لوحة التحكم الشخصية',
                ],
                'inter_lab_transfer' => [
                    'en' => 'Inter Lab Transfer',
                    'sw' => 'Uhamisho wa Maabara',
                    'pt' => 'Transferência Entre Laboratórios',
                    'ar' => 'النقل بين المختبرات',
                ],
                'billing' => [
                    'en' => 'Billing',
                    'sw' => 'Malipo',
                    'pt' => 'Faturamento',
                    'ar' => 'الفوترة',
                ],
                'draft_invoices' => [
                    'en' => 'Draft Invoices',
                    'sw' => 'Rasimu za Ankara',
                    'pt' => 'Faturas em Rascunho',
                    'ar' => 'مسودات الفواتير',
                ],
                'currencies' => [
                    'en' => 'Currencies',
                    'sw' => 'Sarafu',
                    'pt' => 'Moedas',
                    'ar' => 'العملات',
                ],
                'pricelists' => [
                    'en' => 'Pricelists',
                    'sw' => 'Orodha za Bei',
                    'pt' => 'Listas de Preços',
                    'ar' => 'قوائم الأسعار',
                ],
                'tax_regime' => [
                    'en' => 'Tax Regime',
                    'sw' => 'Mpango wa Kodi',
                    'pt' => 'Regime Fiscal',
                    'ar' => 'النظام الضريبي',
                ],
                'quotations' => [
                    'en' => 'Quotations',
                    'sw' => 'Nukuu',
                    'pt' => 'Cotações',
                    'ar' => 'عروض الأسعار',
                ],
                'all_quotes' => [
                    'en' => 'All Quotes',
                    'sw' => 'Nukuu Zote',
                    'pt' => 'Todas as Cotações',
                    'ar' => 'جميع عروض الأسعار',
                ],
                'quotes_in_preparation' => [
                    'en' => 'Quotes In Preparation',
                    'sw' => 'Nukuu Zinazotayarishwa',
                    'pt' => 'Cotações em Preparação',
                    'ar' => 'عروض الأسعار قيد الإعداد',
                ],
                'quotes_in_approval' => [
                    'en' => 'Quotes In Approval',
                    'sw' => 'Nukuu Zinazosubiri Idhini',
                    'pt' => 'Cotações em Aprovação',
                    'ar' => 'عروض الأسعار قيد الاعتماد',
                ],
                'finalised_quotes' => [
                    'en' => 'Finalised Quotes',
                    'sw' => 'Nukuu Zilizokamilika',
                    'pt' => 'Cotações Finalizadas',
                    'ar' => 'عروض الأسعار النهائية',
                ],
                'equipment_requests' => [
                    'en' => 'Equipment Requests',
                    'sw' => 'Maombi ya Vifaa',
                    'pt' => 'Solicitações de Equipamentos',
                    'ar' => 'طلبات المعدات',
                ],
                'qc_workflow' => [
                    'en' => 'QC Workflow',
                    'sw' => 'Mtiririko wa QC',
                    'pt' => 'Fluxo de CQ',
                    'ar' => 'سير عمل مراقبة الجودة',
                ],
                'qc_history' => [
                    'en' => 'QC History',
                    'sw' => 'Historia ya QC',
                    'pt' => 'Histórico de CQ',
                    'ar' => 'سجل مراقبة الجودة',
                ],
                'awaiting_processing' => [
                    'en' => 'Awaiting Processing',
                    'sw' => 'Inasubiri Uchakataji',
                    'pt' => 'Aguardando Processamento',
                    'ar' => 'في انتظار المعالجة',
                ],
                'qc_reports' => [
                    'en' => 'QC Reports',
                    'sw' => 'Ripoti za QC',
                    'pt' => 'Relatórios de CQ',
                    'ar' => 'تقارير مراقبة الجودة',
                ],
                'configurations' => [
                    'en' => 'Configurations',
                    'sw' => 'Misanidi',
                    'pt' => 'Configurações',
                    'ar' => 'الإعدادات',
                ],
                'analytes' => [
                    'en' => 'Analytes',
                    'sw' => 'Vichambuzi',
                    'pt' => 'Analitos',
                    'ar' => 'التحليلات',
                ],
                'labs' => [
                    'en' => 'Labs',
                    'sw' => 'Maabara',
                    'pt' => 'Laboratórios',
                    'ar' => 'المختبرات',
                ],
                'monitoring' => [
                    'en' => 'Monitoring',
                    'sw' => 'Ufuatiliaji',
                    'pt' => 'Monitoramento',
                    'ar' => 'المراقبة',
                ],
                'sample_analysis_stages' => [
                    'en' => 'Sample Analysis Stages',
                    'sw' => 'Hatua za Uchambuzi wa Sampuli',
                    'pt' => 'Etapas de Análise de Amostras',
                    'ar' => 'مراحل تحليل العينات',
                ],
                'sample_types' => [
                    'en' => 'Sample Types',
                    'sw' => 'Aina za Sampuli',
                    'pt' => 'Tipos de Amostra',
                    'ar' => 'أنواع العينات',
                ],
                'formulas' => [
                    'en' => 'WorkSheets',
                    'sw' => 'WorkSheets',
                    'pt' => 'WorkSheets',
                    'ar' => 'WorkSheets',
                ],
                'standards' => [
                    'en' => 'Standards',
                    'sw' => 'Viwango',
                    'pt' => 'Padrões',
                    'ar' => 'المعايير',
                ],
                'reporting_units' => [
                    'en' => 'Reporting Units',
                    'sw' => 'Vitengo vya Kuripoti',
                    'pt' => 'Unidades de Relatório',
                    'ar' => 'وحدات التقارير',
                ],
                'method_validation' => [
                    'en' => 'Method Validation',
                    'sw' => 'Uthibitishaji wa Njia',
                    'pt' => 'Validação de Método',
                    'ar' => 'التحقق من الطريقة',
                ],
                'methods' => [
                    'en' => 'Methods',
                    'sw' => 'Njia',
                    'pt' => 'Métodos',
                    'ar' => 'الطرق',
                ],
                'method_registration' => [
                    'en' => 'Method Registration',
                    'sw' => 'Usajili wa Njia',
                    'pt' => 'Registro de Método',
                    'ar' => 'تسجيل الطريقة',
                ],
                'data_review_analysis' => [
                    'en' => 'Data Review & Analysis',
                    'sw' => 'Mapitio na Uchambuzi wa Data',
                    'pt' => 'Revisão e Análise de Dados',
                    'ar' => 'مراجعة وتحليل البيانات',
                ],
                'uncertainty_budget' => [
                    'en' => 'Uncertainty Budget',
                    'sw' => 'Bajeti ya Kutokuwa na Uhakika',
                    'pt' => 'Orçamento de Incerteza',
                    'ar' => 'ميزانية عدم اليقين',
                ],
                'solutions_monitoring' => [
                    'en' => 'Solutions Monitoring',
                    'sw' => 'Ufuatiliaji wa Suluhisho',
                    'pt' => 'Monitoramento de Soluções',
                    'ar' => 'مراقبة المحاليل',
                ],
                'categories' => [
                    'en' => 'Categories',
                    'sw' => 'Kategoria',
                    'pt' => 'Categorias',
                    'ar' => 'الفئات',
                ],
                'solutions_management' => [
                    'en' => 'Solutions Management',
                    'sw' => 'Usimamizi wa Suluhisho',
                    'pt' => 'Gestão de Soluções',
                    'ar' => 'إدارة المحاليل',
                ],
                'solutions_movement' => [
                    'en' => 'Solutions Movement',
                    'sw' => 'Mwendo wa Suluhisho',
                    'pt' => 'Movimentação de Soluções',
                    'ar' => 'حركة المحاليل',
                ],
                'preparation_tracking' => [
                    'en' => 'Preparation Tracking',
                    'sw' => 'Ufuatiliaji wa Maandalizi',
                    'pt' => 'Rastreamento de Preparação',
                    'ar' => 'تتبع التحضير',
                ],
                'sample_conditions' => [
                    'en' => 'Sample Conditions',
                    'sw' => 'Hali za Sampuli',
                    'pt' => 'Condições da Amostra',
                    'ar' => 'حالات العينة',
                ],
                'checklist_approvals' => [
                    'en' => 'Checklist Approvals',
                    'sw' => 'Idhini za Orodha ya Ukaguzi',
                    'pt' => 'Aprovações de Checklist',
                    'ar' => 'موافقات قائمة التحقق',
                ],
                'zone' => [
                    'en' => 'Zone',
                    'sw' => 'Eneo',
                    'pt' => 'Zona',
                    'ar' => 'المنطقة',
                ],
                'submission_form_templates' => [
                    'en' => 'Submission Form Templates',
                    'sw' => 'Violezo vya Fomu ya Uwasilishaji',
                    'pt' => 'Modelos de Formulário de Submissão',
                    'ar' => 'قوالب نماذج التقديم',
                ],
                'report_templates' => [
                    'en' => 'Report Templates',
                    'sw' => 'Violezo vya Ripoti',
                    'pt' => 'Modelos de Relatório',
                    'ar' => 'قوالب التقارير',
                ],
                'whatsapp_configuration' => [
                    'en' => 'Whatsapp Configuration',
                    'sw' => 'Usanidi wa Whatsapp',
                    'pt' => 'Configuração do Whatsapp',
                    'ar' => 'إعداد واتساب',
                ],
                'reports' => [
                    'en' => 'Reports',
                    'sw' => 'Ripoti',
                    'pt' => 'Relatórios',
                    'ar' => 'التقارير',
                ],
                'centralized_module_reports' => [
                    'en' => 'Centralized Module Reports',
                    'sw' => 'Ripoti za Moduli Zilizokusanywa',
                    'pt' => 'Relatórios Centralizados do Módulo',
                    'ar' => 'تقارير الوحدة المركزية',
                ],
                'lab_reports' => [
                    'en' => 'Lab Reports',
                    'sw' => 'Ripoti za Maabara',
                    'pt' => 'Relatórios do Laboratório',
                    'ar' => 'تقارير المختبر',
                ],
                'workflow_all_samples' => [
                    'en' => 'All Samples',
                    'sw' => 'Sampuli Zote',
                    'pt' => 'Todas as Amostras',
                    'ar' => 'جميع العينات',
                ],
                'workflow_samples_receiving' => [
                    'en' => 'Samples Receiving',
                    'sw' => 'Mapokezi ya Sampuli',
                    'pt' => 'Recebimento de Amostras',
                    'ar' => 'استلام العينات',
                ],
                'workflow_samples_request_review' => [
                    'en' => 'Samples Request Review',
                    'sw' => 'Mapitio ya Ombi la Sampuli',
                    'pt' => 'Revisão de Solicitação de Amostras',
                    'ar' => 'مراجعة طلب العينات',
                ],
                'workflow_reports_in_payment' => [
                    'en' => 'Reports In Payment',
                    'sw' => 'Ripoti katika Malipo',
                    'pt' => 'Relatórios em Pagamento',
                    'ar' => 'التقارير قيد الدفع',
                ],
                'workflow_reports_for_collection' => [
                    'en' => 'Reports for Collection',
                    'sw' => 'Ripoti za Kukusanya',
                    'pt' => 'Relatórios para Coleta',
                    'ar' => 'التقارير للاستلام',
                ],
            ],
            'personnel' => [
                'module_title' => [
                    'en' => 'Personnel Management',
                    'sw' => 'Usimamizi wa Wafanyakazi',
                    'pt' => 'Gestão de Pessoal',
                    'ar' => 'إدارة الموظفين',
                ],
                'dashboard' => [
                    'en' => 'Dashboard',
                    'sw' => 'Dashibodi',
                    'pt' => 'Painel',
                    'ar' => 'لوحة التحكم',
                ],
                'personnel_list' => [
                    'en' => 'Personnel List',
                    'sw' => 'Orodha ya Wafanyakazi',
                    'pt' => 'Lista de Pessoal',
                    'ar' => 'قائمة الموظفين',
                ],
            ],
            'equipment' => [
                'equipment_dashboard' => [
                    'en' => 'Equipment Dashboard',
                    'sw' => 'Dashibodi ya Vifaa',
                    'pt' => 'Painel de Equipamentos',
                    'ar' => 'لوحة تحكم المعدات',
                ],
                'equipment_list' => [
                    'en' => 'Equipment List',
                    'sw' => 'Orodha ya Vifaa',
                    'pt' => 'Lista de Equipamentos',
                    'ar' => 'قائمة المعدات',
                ],
                'equipment_monitoring' => [
                    'en' => 'Equipment Monitoring',
                    'sw' => 'Ufuatiliaji wa Vifaa',
                    'pt' => 'Monitoramento de Equipamentos',
                    'ar' => 'مراقبة المعدات',
                ],
                'equipment_maintenance' => [
                    'en' => 'Equipment Maintenance',
                    'sw' => 'Matengenezo ya Vifaa',
                    'pt' => 'Manutenção de Equipamentos',
                    'ar' => 'صيانة المعدات',
                ],
                'equipment_disposal' => [
                    'en' => 'Equipment Disposal',
                    'sw' => 'Uondoaji wa Vifaa',
                    'pt' => 'Descarte de Equipamentos',
                    'ar' => 'التخلص من المعدات',
                ],
                'asset_depreciation' => [
                    'en' => 'Asset Depreciation',
                    'sw' => 'Uchakavu wa Mali',
                    'pt' => 'Depreciação de Ativos',
                    'ar' => 'إهلاك الأصول',
                ],
                'depreciation_list' => [
                    'en' => 'Depreciation List',
                    'sw' => 'Orodha ya Uchakavu',
                    'pt' => 'Lista de Depreciação',
                    'ar' => 'قائمة الإهلاك',
                ],
                'depreciation_methods' => [
                    'en' => 'Depreciation Methods',
                    'sw' => 'Mbinu za Uchakavu',
                    'pt' => 'Métodos de Depreciação',
                    'ar' => 'طرق الإهلاك',
                ],
            ],
            'inventory' => [
                'module_name' => ['en' => 'Inventory Management', 'sw' => 'Usimamizi wa Hesabu', 'pt' => 'Gestão de Inventário', 'ar' => 'إدارة المخزون'],
                'dashboard' => ['en' => 'Dashboard', 'sw' => 'Dashibodi', 'pt' => 'Painel', 'ar' => 'لوحة التحكم'],
                'select_location' => ['en' => 'Select Location', 'sw' => 'Chagua Eneo', 'pt' => 'Selecionar Local', 'ar' => 'اختر الموقع'],
                'location' => ['en' => 'Location', 'sw' => 'Eneo', 'pt' => 'Local', 'ar' => 'الموقع'],
                'alerts' => ['en' => 'Alerts', 'sw' => 'Tahadhari', 'pt' => 'Alertas', 'ar' => 'التنبيهات'],
                'no_alerts' => ['en' => 'No Alerts', 'sw' => 'Hakuna Tahadhari', 'pt' => 'Sem Alertas', 'ar' => 'لا توجد تنبيهات'],
                'restock_alerts' => ['en' => 'Restock', 'sw' => 'Jaza Tena', 'pt' => 'Reabastecer', 'ar' => 'إعادة التخزين'],
                'send_reorder_notifications' => ['en' => 'Send Re-order Notifications', 'sw' => 'Tuma Arifa za Kuagiza Tena', 'pt' => 'Enviar Notificações de Reabastecimento', 'ar' => 'إرسال إشعارات إعادة الطلب'],
                'approval_requests' => ['en' => 'Approval Requests', 'sw' => 'Maombi ya Idhini', 'pt' => 'Solicitações de Aprovação', 'ar' => 'طلبات الموافقة'],
                'request_to_order' => ['en' => 'Request to Order', 'sw' => 'Ombi la Kuagiza', 'pt' => 'Solicitação de Compra', 'ar' => 'طلب الشراء'],
                'request_to_store' => ['en' => 'Request to Store', 'sw' => 'Ombi la Ghala', 'pt' => 'Solicitação ao Armazém', 'ar' => 'طلب إلى المخزن'],
                'loan_lend' => ['en' => 'Loan/Lend', 'sw' => 'Mkopo/Kukopa', 'pt' => 'Empréstimo/Alugar', 'ar' => 'الإعارة/الاستعارة'],
                'lend' => ['en' => 'Lend', 'sw' => 'Kukopa', 'pt' => 'Emprestar', 'ar' => 'إعارة'],
                'loan' => ['en' => 'Loan', 'sw' => 'Mkopo', 'pt' => 'Empréstimo', 'ar' => 'استعارة'],
                'categories' => ['en' => 'Categories', 'sw' => 'Kategoria', 'pt' => 'Categorias', 'ar' => 'الفئات'],
                'inventory_movement' => ['en' => 'Inventory Movement', 'sw' => 'Mwendo wa Hesabu', 'pt' => 'Movimentação de Inventário', 'ar' => 'حركة المخزون'],
                'departments' => ['en' => 'Departments', 'sw' => 'Idara', 'pt' => 'Departamentos', 'ar' => 'الأقسام'],
                'suppliers' => ['en' => 'Suppliers', 'sw' => 'Wasambazaji', 'pt' => 'Fornecedores', 'ar' => 'الموردون'],
                'store' => ['en' => 'Store', 'sw' => 'Ghala', 'pt' => 'Armazém', 'ar' => 'المخزن'],
                'stock_taking' => ['en' => 'Stock Taking', 'sw' => 'Uchukuzi wa Hesabu', 'pt' => 'Inventário Físico', 'ar' => 'جرد المخزون'],
                'stock_transfer' => ['en' => 'Stock Transfer', 'sw' => 'Uhamisho wa Hesabu', 'pt' => 'Transferência de Estoque', 'ar' => 'نقل المخزون'],
                'reports' => ['en' => 'Reports', 'sw' => 'Ripoti', 'pt' => 'Relatórios', 'ar' => 'التقارير'],
                'unit_of_measure' => ['en' => 'Unit of Measure', 'sw' => 'Kipimo', 'pt' => 'Unidade de Medida', 'ar' => 'وحدة القياس'],
                'configurations' => ['en' => 'Configurations', 'sw' => 'Misanidi', 'pt' => 'Configurações', 'ar' => 'الإعدادات'],
                'material_type' => ['en' => 'Material Type', 'sw' => 'Aina ya Nyenzo', 'pt' => 'Tipo de Material', 'ar' => 'نوع المادة'],
                'currency' => ['en' => 'Currency', 'sw' => 'Sarafu', 'pt' => 'Moeda', 'ar' => 'العملة'],
                'currency_conversion' => ['en' => 'Currency Conversion', 'sw' => 'Ubadilishaji wa Sarafu', 'pt' => 'Conversão de Moeda', 'ar' => 'تحويل العملة'],
                'uom_conversion' => ['en' => 'UoM Conversion', 'sw' => 'Ubadilishaji wa Kipimo', 'pt' => 'Conversão de UdM', 'ar' => 'تحويل وحدة القياس'],
                'in_development' => ['en' => 'In Development', 'sw' => 'Inatengenezwa', 'pt' => 'Em Desenvolvimento', 'ar' => 'قيد التطوير'],
                'restock_notifications' => ['en' => 'Restock Notifications', 'sw' => 'Arifa za Kujaza Tena', 'pt' => 'Notificações de Reabastecimento', 'ar' => 'إشعارات إعادة التخزين'],
                'inventory_activity' => ['en' => 'Inventory Activity', 'sw' => 'Shughuli za Hesabu', 'pt' => 'Atividade de Inventário', 'ar' => 'نشاط المخزون'],
                'last_30_days' => ['en' => 'Last 30 Days', 'sw' => 'Siku 30 Zilizopita', 'pt' => 'Últimos 30 Dias', 'ar' => 'آخر 30 يومًا'],
                'stock_in' => ['en' => 'Stock In', 'sw' => 'Hesabu Inayoingia', 'pt' => 'Entrada de Estoque', 'ar' => 'وارد المخزون'],
                'stock_out' => ['en' => 'Stock Out', 'sw' => 'Hesabu Inayotoka', 'pt' => 'Saída de Estoque', 'ar' => 'صادر المخزون'],
                'workflow_purchase_request' => ['en' => 'Purchase Request', 'sw' => 'Ombi la Ununuzi', 'pt' => 'Solicitação de Compra', 'ar' => 'طلب شراء'],
                'workflow_request_for_quotation' => ['en' => 'Request for Quotation', 'sw' => 'Ombi la Nukuu', 'pt' => 'Solicitação de Cotação', 'ar' => 'طلب عرض سعر'],
                'workflow_purchase_orders' => ['en' => 'Purchase Orders', 'sw' => 'Maagizo ya Ununuzi', 'pt' => 'Ordens de Compra', 'ar' => 'أوامر الشراء'],
                'workflow_goods_receipt' => ['en' => 'Goods Receipt', 'sw' => 'Mapokezi ya Bidhaa', 'pt' => 'Recebimento de Mercadorias', 'ar' => 'استلام البضائع'],
                'workflow_goods_return' => ['en' => 'Goods Return', 'sw' => 'Kurudisha Bidhaa', 'pt' => 'Devolução de Mercadorias', 'ar' => 'إرجاع البضائع'],
                'workflow_request_to_store' => ['en' => 'Request to Store', 'sw' => 'Ombi la Ghala', 'pt' => 'Solicitação ao Armazém', 'ar' => 'طلب إلى المخزن'],
                'workflow_material_issuance' => ['en' => 'Material Issuance', 'sw' => 'Kutolewa kwa Nyenzo', 'pt' => 'Emissão de Material', 'ar' => 'صرف المواد'],
            ],
            'planner' => [
                'module_name' => ['en' => 'System Planner', 'sw' => 'Mpangaji wa Mfumo', 'pt' => 'Planejador do Sistema', 'ar' => 'مخطط النظام'],
                'dashboard' => ['en' => 'Dashboard', 'sw' => 'Dashibodi', 'pt' => 'Painel', 'ar' => 'لوحة التحكم'],
                'dashboard_subtitle' => ['en' => 'Overview of sampling schedules, collections, KPIs, and tasks', 'sw' => 'Muhtasari wa ratiba za sampuli, mikusanyo, KPI, na kazi', 'pt' => 'Visão geral de agendas de amostragem, coletas, KPIs e tarefas', 'ar' => 'نظرة عامة على جداول أخذ العينات والمجمعات ومؤشرات الأداء والمهام'],
                'schedule_sampling' => ['en' => 'Sampling Schedule', 'sw' => 'Ratiba ya Sampuli', 'pt' => 'Agenda de Amostragem', 'ar' => 'جدول أخذ العينات'],
                'sampling_schedule' => ['en' => 'Sampling Schedule', 'sw' => 'Ratiba ya Sampuli', 'pt' => 'Agenda de Amostragem', 'ar' => 'جدول أخذ العينات'],
                'fill_sampling_forms' => ['en' => 'Fill Sampling Forms', 'sw' => 'Jaza Fomu za Sampuli', 'pt' => 'Preencher Formulários de Amostragem', 'ar' => 'تعبئة نماذج أخذ العينات'],
                'fill_form' => ['en' => 'Fill form', 'sw' => 'Jaza fomu', 'pt' => 'Preencher formulário', 'ar' => 'تعبئة النموذج'],
                'fill_sampling_forms_subtitle' => ['en' => 'Fill sampling forms for scheduled collections', 'sw' => 'Jaza fomu za sampuli kwa mikusanyo iliyopangwa', 'pt' => 'Preencha formulários de amostragem para coletas agendadas', 'ar' => 'عبّئ نماذج أخذ العينات للمجموعات المجدولة'],
                'new_sampling_schedule' => ['en' => 'New Sampling Schedule', 'sw' => 'Ratiba Mpya ya Sampuli', 'pt' => 'Nova Agenda de Amostragem', 'ar' => 'جدول أخذ عينات جديد'],
                'calendar' => ['en' => 'Calendar', 'sw' => 'Kalenda', 'pt' => 'Calendário', 'ar' => 'التقويم'],
                'actual_collections' => ['en' => 'Actual Collections', 'sw' => 'Mikusanyo Halisi', 'pt' => 'Coletas Reais', 'ar' => 'الجمع الفعلي'],
                'kpi_reports' => ['en' => 'KPI Reports', 'sw' => 'Ripoti za KPI', 'pt' => 'Relatórios de KPI', 'ar' => 'تقارير مؤشرات الأداء'],
                'tasks' => ['en' => 'Tasks', 'sw' => 'Kazi', 'pt' => 'Tarefas', 'ar' => 'المهام'],
                'sampling_schedules' => ['en' => 'Sampling Schedules', 'sw' => 'Ratiba za Sampuli', 'pt' => 'Agendas de Amostragem', 'ar' => 'جداول أخذ العينات'],
                'sampling_schedules_subtitle' => ['en' => 'Plan and coordinate sampling runs for CRM customers', 'sw' => 'Panga na uratibu safari za sampuli kwa wateja wa CRM', 'pt' => 'Planeje e coordene coletas de amostras para clientes do CRM', 'ar' => 'خطط ونسق جولات أخذ العينات لعملاء إدارة علاقات العملاء'],
                'upcoming_7_days' => ['en' => 'Upcoming (7 days)', 'sw' => 'Zijazo (siku 7)', 'pt' => 'Próximas (7 dias)', 'ar' => 'القادمة (7 أيام)'],
                'overdue_schedules' => ['en' => 'Overdue', 'sw' => 'Zimechelewa', 'pt' => 'Atrasadas', 'ar' => 'متأخرة'],
                'open_tasks' => ['en' => 'Open Tasks', 'sw' => 'Kazi Funguliwa', 'pt' => 'Tarefas Abertas', 'ar' => 'المهام المفتوحة'],
                'completed_tasks' => ['en' => 'Completed Tasks', 'sw' => 'Kazi Zilizokamilika', 'pt' => 'Tarefas Concluídas', 'ar' => 'المهام المكتملة'],
                'last_30_days' => ['en' => 'Last 30 days', 'sw' => 'Siku 30 zilizopita', 'pt' => 'Últimos 30 dias', 'ar' => 'آخر 30 يوماً'],
                'collection_status_30d' => ['en' => 'Collection status (30 days)', 'sw' => 'Hali ya ukusanyaji (siku 30)', 'pt' => 'Status de coleta (30 dias)', 'ar' => 'حالة الجمع (30 يوماً)'],
                'schedules_by_frequency' => ['en' => 'Schedules by frequency', 'sw' => 'Ratiba kwa mzunguko', 'pt' => 'Agendas por frequência', 'ar' => 'الجداول حسب التكرار'],
                'task_status_breakdown' => ['en' => 'Task status', 'sw' => 'Hali ya kazi', 'pt' => 'Status das tarefas', 'ar' => 'حالة المهام'],
                'scheduled_vs_collected_trend' => ['en' => 'Scheduled vs collected (6 months)', 'sw' => 'Zilizopangwa dhidi ya zilizokusanywa (miezi 6)', 'pt' => 'Agendado vs coletado (6 meses)', 'ar' => 'المجدول مقابل المجمع (6 أشهر)'],
                'quick_actions' => ['en' => 'Quick actions', 'sw' => 'Vitendo vya haraka', 'pt' => 'Ações rápidas', 'ar' => 'إجراءات سريعة'],
                'top_clients' => ['en' => 'Top clients by schedules', 'sw' => 'Wateja wakuu kwa ratiba', 'pt' => 'Principais clientes por agendas', 'ar' => 'أبرز العملاء حسب الجداول'],
                'personnel_workload' => ['en' => 'Personnel workload', 'sw' => 'Mzigo wa wafanyakazi', 'pt' => 'Carga de trabalho do pessoal', 'ar' => 'عبء عمل الموظفين'],
                'upcoming_schedules' => ['en' => 'Upcoming schedules', 'sw' => 'Ratiba zijazo', 'pt' => 'Agendas próximas', 'ar' => 'الجداول القادمة'],
                'my_schedules' => ['en' => 'My Schedules', 'sw' => 'Ratiba Zangu', 'pt' => 'Minhas Agendas', 'ar' => 'جداولي'],
                'my_schedules_subtitle' => ['en' => 'Sampling runs assigned to you', 'sw' => 'Safari za sampuli zilizokupwa', 'pt' => 'Coletas atribuídas a você', 'ar' => 'جولات أخذ العينات المسندة إليك'],
                'samples_collected_progress' => ['en' => ':collected of :scheduled samples collected', 'sw' => 'Sampuli :collected kati ya :scheduled zimekusanywa', 'pt' => ':collected de :scheduled amostras coletadas', 'ar' => 'تم جمع :collected من :scheduled عينات'],
                'recent_collections' => ['en' => 'Recent collections', 'sw' => 'Mikusanyo ya hivi karibuni', 'pt' => 'Coletas recentes', 'ar' => 'عمليات الجمع الأخيرة'],
                'view_all' => ['en' => 'View all', 'sw' => 'Angalia zote', 'pt' => 'Ver tudo', 'ar' => 'عرض الكل'],
                'no_upcoming_schedules' => ['en' => 'No upcoming schedules in the next 7 days.', 'sw' => 'Hakuna ratiba zijazo katika siku 7 zijazo.', 'pt' => 'Nenhuma agenda próxima nos próximos 7 dias.', 'ar' => 'لا توجد جداول قادمة خلال الأيام السبعة القادمة.'],
                'no_overdue_schedules' => ['en' => 'No overdue schedules.', 'sw' => 'Hakuna ratiba zilizochelewa.', 'pt' => 'Nenhuma agenda atrasada.', 'ar' => 'لا توجد جداول متأخرة.'],
                'no_recent_collections' => ['en' => 'No collections yet.', 'sw' => 'Bado hakuna mikusanyo.', 'pt' => 'Ainda não há coletas.', 'ar' => 'لا توجد عمليات جمع بعد.'],
                'no_chart_data' => ['en' => 'No data to display.', 'sw' => 'Hakuna data ya kuonyesha.', 'pt' => 'Sem dados para exibir.', 'ar' => 'لا توجد بيانات للعرض.'],
                'actual_collections_subtitle_all' => ['en' => 'All completed sampling collections for your organization', 'sw' => 'Mikusanyo yote ya sampuli yaliyokamilika kwa shirika lako', 'pt' => 'Todas as coletas de amostras concluídas para sua organização', 'ar' => 'جميع عمليات جمع العينات المكتملة لمؤسستك'],
                'actual_collections_subtitle_assigned' => ['en' => 'Sampling collections assigned to you or submitted by you', 'sw' => 'Mikusanyo ya sampuli yaliyokupwa au uliyowasilisha', 'pt' => 'Coletas de amostras atribuídas a você ou enviadas por você', 'ar' => 'عمليات جمع العينات المسندة إليك أو المقدمة منك'],
                'viewing_all_collections' => ['en' => 'Viewing all collections', 'sw' => 'Inaonyesha mikusanyo yote', 'pt' => 'Visualizando todas as coletas', 'ar' => 'عرض جميع عمليات الجمع'],
                'kpi_reports_subtitle' => ['en' => 'Scheduled vs. collected sampling performance across clients and contracts', 'sw' => 'Utendaji wa sampuli zilizopangwa dhidi ya zilizokusanywa kwa wateja na mikataba', 'pt' => 'Desempenho de amostragem agendada vs. coletada entre clientes e contratos', 'ar' => 'أداء أخذ العينات المجدولة مقابل المجمعة عبر العملاء والعقود'],
                'search_placeholder' => ['en' => 'Search by title, location, client...', 'sw' => 'Tafuta kwa kichwa, eneo, mteja...', 'pt' => 'Pesquisar por título, local, cliente...', 'ar' => 'ابحث بالعنوان أو الموقع أو العميل...'],
                'kpi_search_placeholder' => ['en' => 'Client, title, location...', 'sw' => 'Mteja, kichwa, eneo...', 'pt' => 'Cliente, título, local...', 'ar' => 'العميل، العنوان، الموقع...'],
                'show_filters' => ['en' => 'Show Filters', 'sw' => 'Onyesha Vichujio', 'pt' => 'Mostrar Filtros', 'ar' => 'إظهار الفلاتر'],
                'hide_filters' => ['en' => 'Hide Filters', 'sw' => 'Ficha Vichujio', 'pt' => 'Ocultar Filtros', 'ar' => 'إخفاء الفلاتر'],
                'export_excel' => ['en' => 'Export Excel', 'sw' => 'Hamisha Excel', 'pt' => 'Exportar Excel', 'ar' => 'تصدير Excel'],
                'export_pdf' => ['en' => 'Export PDF', 'sw' => 'Hamisha PDF', 'pt' => 'Exportar PDF', 'ar' => 'تصدير PDF'],
                'export_csv' => ['en' => 'Export CSV', 'sw' => 'Hamisha CSV', 'pt' => 'Exportar CSV', 'ar' => 'تصدير CSV'],
                'date_from' => ['en' => 'Date From', 'sw' => 'Tarehe Kutoka', 'pt' => 'Data De', 'ar' => 'التاريخ من'],
                'date_to' => ['en' => 'Date To', 'sw' => 'Tarehe Hadi', 'pt' => 'Data Até', 'ar' => 'التاريخ إلى'],
                'client' => ['en' => 'Client', 'sw' => 'Mteja', 'pt' => 'Cliente', 'ar' => 'العميل'],
                'all_clients' => ['en' => 'All Clients', 'sw' => 'Wateja Wote', 'pt' => 'Todos os Clientes', 'ar' => 'جميع العملاء'],
                'frequency' => ['en' => 'Frequency', 'sw' => 'Mzunguko', 'pt' => 'Frequência', 'ar' => 'التكرار'],
                'all_frequencies' => ['en' => 'All Frequencies', 'sw' => 'Mzunguko Wote', 'pt' => 'Todas as Frequências', 'ar' => 'جميع التكرارات'],
                'personnel' => ['en' => 'Personnel', 'sw' => 'Wafanyakazi', 'pt' => 'Pessoal', 'ar' => 'الموظفون'],
                'all_personnel' => ['en' => 'All personnel', 'sw' => 'Wafanyakazi wote', 'pt' => 'Todo o pessoal', 'ar' => 'جميع الموظفين'],
                'reset_filters' => ['en' => 'Reset Filters', 'sw' => 'Weka Upya Vichujio', 'pt' => 'Redefinir Filtros', 'ar' => 'إعادة تعيين الفلاتر'],
                'reset_all_filters' => ['en' => 'Reset All Filters', 'sw' => 'Weka Upya Vichujio Vyote', 'pt' => 'Redefinir Todos os Filtros', 'ar' => 'إعادة تعيين جميع الفلاتر'],
                'total_schedules' => ['en' => 'Total Schedules', 'sw' => 'Jumla ya Ratiba', 'pt' => 'Total de Agendamentos', 'ar' => 'إجمالي الجداول'],
                'fully_collected' => ['en' => 'Fully Collected', 'sw' => 'Imekusanywa Kikamilifu', 'pt' => 'Totalmente Coletado', 'ar' => 'تم الجمع بالكامل'],
                'partial' => ['en' => 'Partial', 'sw' => 'Sehemu', 'pt' => 'Parcial', 'ar' => 'جزئي'],
                'pending' => ['en' => 'Pending', 'sw' => 'Inasubiri', 'pt' => 'Pendente', 'ar' => 'قيد الانتظار'],
                'collection_rate' => ['en' => 'Collection Rate', 'sw' => 'Kiwango cha Ukusanyaji', 'pt' => 'Taxa de Coleta', 'ar' => 'معدل الجمع'],
                'samples_collected_vs_scheduled' => ['en' => 'Samples collected vs scheduled', 'sw' => 'Sampuli zilizokusanywa dhidi ya zilizopangwa', 'pt' => 'Amostras coletadas vs agendadas', 'ar' => 'العينات المجمعة مقابل المجدولة'],
                'additional_filters' => ['en' => 'Additional Filters', 'sw' => 'Vichujio vya Ziada', 'pt' => 'Filtros Adicionais', 'ar' => 'فلاتر إضافية'],
                'close' => ['en' => 'Close', 'sw' => 'Funga', 'pt' => 'Fechar', 'ar' => 'إغلاق'],
                'more' => ['en' => 'More', 'sw' => 'Zaidi', 'pt' => 'Mais', 'ar' => 'المزيد'],
                'showing_records' => ['en' => 'Showing :count record(s)', 'sw' => 'Inaonyesha rekodi :count', 'pt' => 'Mostrando :count registro(s)', 'ar' => 'عرض :count سجل'],
                'date' => ['en' => 'Date', 'sw' => 'Tarehe', 'pt' => 'Data', 'ar' => 'التاريخ'],
                'contract_validity' => ['en' => 'Contract Validity', 'sw' => 'Uhalali wa Mkataba', 'pt' => 'Validade do Contrato', 'ar' => 'صلاحية العقد'],
                'contact' => ['en' => 'Contact', 'sw' => 'Mawasiliano', 'pt' => 'Contato', 'ar' => 'جهة الاتصال'],
                'sample_categories' => ['en' => 'Sample Categories', 'sw' => 'Kategoria za Sampuli', 'pt' => 'Categorias de Amostra', 'ar' => 'فئات العينات'],
                'sample_details' => ['en' => 'Sample Details', 'sw' => 'Maelezo ya Sampuli', 'pt' => 'Detalhes da Amostra', 'ar' => 'تفاصيل العينة'],
                'no_samples' => ['en' => 'No. Samples', 'sw' => 'Idadi ya Sampuli', 'pt' => 'Nº de Amostras', 'ar' => 'عدد العينات'],
                'parameters' => ['en' => 'Parameters', 'sw' => 'Vigezo', 'pt' => 'Parâmetros', 'ar' => 'المعاملات'],
                'status' => ['en' => 'Status', 'sw' => 'Hali', 'pt' => 'Status', 'ar' => 'الحالة'],
                'scheduled' => ['en' => 'Scheduled', 'sw' => 'Imepangwa', 'pt' => 'Agendado', 'ar' => 'مجدول'],
                'collected' => ['en' => 'Collected', 'sw' => 'Imekusanywa', 'pt' => 'Coletado', 'ar' => 'تم الجمع'],
                'no_records_found' => ['en' => 'No records found for the selected filters.', 'sw' => 'Hakuna rekodi zilizopatikana kwa vichujio vilivyochaguliwa.', 'pt' => 'Nenhum registro encontrado para os filtros selecionados.', 'ar' => 'لم يتم العثور على سجلات للفلاتر المحددة.'],
                'no_records_match' => ['en' => 'No records match your current filters.', 'sw' => 'Hakuna rekodi zinazolingana na vichujio vyako vya sasa.', 'pt' => 'Nenhum registro corresponde aos seus filtros atuais.', 'ar' => 'لا توجد سجلات تطابق الفلاتر الحالية.'],
                'sched_coll' => ['en' => 'Sched / Coll', 'sw' => 'Pangwa / Kusanywa', 'pt' => 'Agend / Col', 'ar' => 'مجدول / مجمع'],
            ],
        ];

        $count = 0;
        $touchedGroups = [];

        foreach ($groups as $group => $items) {
            foreach ($items as $key => $text) {
                TranslationLanguageLine::query()->updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['text' => $text]
                );
                $count++;
                $touchedGroups[$group] = true;
            }
        }

        foreach (array_keys($touchedGroups) as $group) {
            TranslationLanguageLine::flushGroupCacheForAllLocales($group);
        }

        // Spatie caches translation groups forever; clear app cache so production
        // workers pick up newly seeded keys immediately.
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        } catch (\Throwable) {
            // Ignore when cache clear is unavailable in constrained environments.
        }

        $this->command?->info("Module navigation translations seeded: {$count} keys");
        $this->command?->info('Translation cache flushed. If keys still show raw, run: php artisan cache:clear && php artisan view:clear');
    }
}
