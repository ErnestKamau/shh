<?php

namespace Database\Seeders\Setup\Languages;

use App\Models\System\TranslationLanguageLine;
use Illuminate\Database\Seeder;

class MasLanguageDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $translations = array (
  'ai' => 
  array (
    'active' => 
    array (
      'en' => 'Active',
      'sw' => 'Amilifu',
    ),
    'active_batches_monitored' => 
    array (
      'en' => 'active batches monitored',
      'sw' => 'bachi zinazoendelea kufuatiliwa',
    ),
    'alerts_drift' => 
    array (
      'en' => 'Active Alerts & Drift Monitoring',
      'sw' => 'Tahadhari Zinazoendelea na Ufuatiliaji wa Mabadiliko',
    ),
    'anomalies_detected' => 
    array (
      'en' => 'Anomalies Detected',
      'sw' => 'Hitilafu Zilizogunduliwa',
    ),
    'delayed' => 
    array (
      'en' => 'FAIL',
      'sw' => 'IMEFELI',
    ),
    'framework' => 
    array (
      'en' => 'Framework',
      'sw' => 'Mfumo wa Kazi',
    ),
    'health_score' => 
    array (
      'en' => 'Health Score',
      'sw' => 'Alama ya Afya',
    ),
    'healthy_status' => 
    array (
      'en' => 'All AI systems functioning within normal parameters. No active drift detected.',
      'sw' => 'Mifumo yote ya Akili Bandia inafanya kazi ndani ya vigezo vya kawaida. Hakuna mabadiliko yaliyogunduliwa.',
    ),
    'hits' => 
    array (
      'en' => 'hits',
      'sw' => 'matokeo',
    ),
    'inactive' => 
    array (
      'en' => 'Inactive',
      'sw' => 'Isiyoamilifu',
    ),
    'intent_detection' => 
    array (
      'en' => 'Intent Detection',
      'sw' => 'Matumizi ya Upendeleo',
    ),
    'inventory_health_index' => 
    array (
      'en' => 'Inventory Health Index',
      'sw' => 'Kielelezo cha Afya ya Mali',
    ),
    'last_deployment' => 
    array (
      'en' => 'Last Deployment',
      'sw' => 'Usambazaji wa Mwisho',
    ),
    'lims_predictive' => 
    array (
      'en' => 'LIMS Predictive Analytics',
      'sw' => 'Uchambuzi wa Kitabiri wa LIMS',
    ),
    'model_id' => 
    array (
      'en' => 'Model ID',
      'sw' => 'ID ya Modeli',
    ),
    'model_inference' => 
    array (
      'en' => 'Model Inference',
      'sw' => 'Usahihi wa Uelekezaji',
    ),
    'model_performance' => 
    array (
      'en' => 'Model Performance Matrix',
      'sw' => 'Jedwali la Utendaji wa Modeli',
    ),
    'model_registry' => 
    array (
      'en' => 'ML Model Registry & Governance',
      'sw' => 'Sajili ya Modeli ya ML na Utawala',
    ),
    'models_registered' => 
    array (
      'en' => 'Models Registered',
      'sw' => 'Modeli Zilizosajiliwa',
    ),
    'name' => 
    array (
      'en' => 'Name',
      'sw' => 'Jina',
    ),
    'near_expiry' => 
    array (
      'en' => 'Items approaching expiry',
      'sw' => 'Vitu vinavyokaribia kuisha muda',
    ),
    'no_intent_data' => 
    array (
      'en' => 'No intent data recorded yet.',
      'sw' => 'Hakuna data ya nia iliyorekodiwa bado.',
    ),
    'no_performance_data' => 
    array (
      'en' => 'No AI model performance data recorded yet.',
      'sw' => 'Hakuna data ya utendaji wa modeli ya AI iliyorekodiwa bado.',
    ),
    'no_registry_records' => 
    array (
      'en' => 'No model registry records available.',
      'sw' => 'Hakuna rekodi za usajili wa modeli zinazopatikana.',
    ),
    'on_time' => 
    array (
      'en' => 'MET',
      'sw' => 'IMEFIKIWA',
    ),
    'on_time_performance' => 
    array (
      'en' => 'On-Time Performance',
      'sw' => 'Utendaji wa Wakati',
    ),
    'operational_kpis' => 
    array (
      'en' => 'Operational KPIs',
      'sw' => 'Vipimo vya Uendeshaji (KPIs)',
    ),
    'performance_placeholder_sub' => 
    array (
      'en' => 'Metrics will appear here once the system starts processing requests.',
      'sw' => 'Vipimo vitaonekana hapa mara tu mfumo unapoanza kuchakata maombi.',
    ),
    'predictive_inventory' => 
    array (
      'en' => 'Inventory Drift: Detected potential stockout in 3 high-priority chemicals within 14 days.',
      'sw' => 'Drift ya Hesabu: Iligunduliwa uwezekano wa upungufu wa bidhaa katika kemikali 3 za kipaumbele cha juu ndani ya siku 14.',
    ),
    'predictive_qc' => 
    array (
      'en' => 'QC Stability: 2 critical anomalies flagged in current validation cycle.',
      'sw' => 'Uthabiti wa QC: Vighairi 2 muhimu vimealamishwa katika mzunguko wa sasa wa uhakiki.',
    ),
    'predictive_sla' => 
    array (
      'en' => 'SLA Compliance Projection: Currently at 98.4% across all active batches.',
      'sw' => 'Utabiri wa Utekelezaji wa SLA: Hivi sasa ni 98.4% katika bachi zote amilifu.',
    ),
    'predictive_subtitle' => 
    array (
      'en' => 'Analytical insights derived from deep-learning models processing laboratory benchmarks.',
      'sw' => 'Maarifa ya uchambuzi yanayotokana na mifano ya deep-learning inayopata vigezo vya maabara.',
    ),
    'qc_drift' => 
    array (
      'en' => 'QC Drift & Stability',
      'sw' => 'Mabadiliko ya QC na Utulivu',
    ),
    'quota_usage' => 
    array (
      'en' => 'Quota Usage',
      'sw' => 'Matumizi ya Upendeleo',
    ),
    'replenishment_alerts' => 
    array (
      'en' => 'Replenishment Alerts',
      'sw' => 'Tahadhari za Kujaza Tena',
    ),
    'routing_accuracy' => 
    array (
      'en' => 'Routing Accuracy',
      'sw' => 'Usahihi wa Uelekezaji',
    ),
    'sla_compliance' => 
    array (
      'en' => 'SLA Compliance',
      'sw' => 'Uzingatiaji wa SLA',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'subtitle' => 
    array (
      'en' => 'Consolidated analytics and predictive insights across all modules.',
      'sw' => 'Uchambuzi uliounganishwa na utabiri wa maarifa kwenye moduli zote.',
    ),
    'success_rate' => 
    array (
      'en' => 'Success Rate',
      'sw' => 'Kiwango cha Mafanikio',
    ),
    'system_status' => 
    array (
      'en' => 'System Status',
      'sw' => 'Hali ya Mfumo',
    ),
    'title' => 
    array (
      'en' => 'AI Intelligence Hub',
      'sw' => 'Kitivo cha Akili Bandia',
    ),
    'top_intents' => 
    array (
      'en' => 'Top User Intents',
      'sw' => 'Nia Kuu za Watumiaji',
    ),
    'type' => 
    array (
      'en' => 'Type',
      'sw' => 'Aina',
    ),
    'version' => 
    array (
      'en' => 'Version',
      'sw' => 'Toleo',
    ),
    'view_logs' => 
    array (
      'en' => 'View Detailed Logs',
      'sw' => 'Angalia Kumbukumbu za Kina',
    ),
  ),
  'audit' => 
  array (
    'actions' => 
    array (
      'en' => 'Actions',
      'sw' => 'Vitendo',
    ),
    'actor' => 
    array (
      'en' => 'Actor',
      'sw' => 'Mhusika',
    ),
    'advanced_search' => 
    array (
      'en' => 'Advanced Search',
      'sw' => 'Utafutaji wa Juu',
    ),
    'entity' => 
    array (
      'en' => 'Entity',
      'sw' => 'Chombo',
    ),
    'event' => 
    array (
      'en' => 'Event',
      'sw' => 'Tukio',
    ),
    'ip_address' => 
    array (
      'en' => 'IP Address',
      'sw' => 'Anwani ya IP',
    ),
    'no_logs' => 
    array (
      'en' => 'No audit logs found.',
      'sw' => 'Hakuna logi za ukaguzi zilizopatikana.',
    ),
    'recent_activity' => 
    array (
      'en' => 'Recent System Activity (Last 50 Events)',
      'sw' => 'Shughuli za Hivi Karibuni za Mfumo (Matukio 50 ya Mwisho)',
    ),
    'subtitle' => 
    array (
      'en' => 'Transparent traceability of all critical system actions and modifications.',
      'sw' => 'Ufuatiliaji wa wazi wa vitendo vyote muhimu vya mfumo na marekebisho.',
    ),
    'system' => 
    array (
      'en' => 'System',
      'sw' => 'Mfumo',
    ),
    'timestamp' => 
    array (
      'en' => 'Timestamp',
      'sw' => 'Muda',
    ),
    'title' => 
    array (
      'en' => 'Audit & Compliance Log',
      'sw' => 'Logi ya Ukaguzi na Uzingatiaji',
    ),
    'total_events' => 
    array (
      'en' => 'Total Events',
      'sw' => 'Jumla ya Matukio',
    ),
    'view_changes' => 
    array (
      'en' => 'View Changes',
      'sw' => 'Angalia Mabadiliko',
    ),
  ),
  'common' => 
  array (
    'active' => 
    array (
      'en' => 'Active',
      'sw' => 'Zinazoendelea',
    ),
    'advanced_search' => 
    array (
      'en' => 'Advanced Search',
      'sw' => 'Utafutaji wa Juu',
    ),
    'analyte' => 
    array (
      'en' => 'Analyte',
      'sw' => 'Analiti',
    ),
    'batch' => 
    array (
      'en' => 'Batch',
      'sw' => 'Bachi',
    ),
    'brand_footer' => 
    array (
      'en' => 'GCLA IMARA LIMS',
      'sw' => 'GCLA IMARA LIMS',
    ),
    'brand_name' => 
    array (
      'en' => 'GCLA IMARA',
      'sw' => 'GCLA IMARA',
    ),
    'client' => 
    array (
      'en' => 'Client',
      'sw' => 'Mteja',
    ),
    'count' => 
    array (
      'en' => 'Count',
      'sw' => 'Idadi',
    ),
    'critical' => 
    array (
      'en' => 'Critical',
      'sw' => 'Hatari',
    ),
    'daily_check' => 
    array (
      'en' => 'Success Rate',
      'sw' => 'Kiwango cha Mafanikio',
    ),
    'details' => 
    array (
      'en' => 'Details',
      'sw' => 'Maelezo',
    ),
    'download' => 
    array (
      'en' => 'Download',
      'sw' => 'Pakua',
    ),
    'download_report' => 
    array (
      'en' => 'Download Report',
      'sw' => 'Pakua Ripoti',
    ),
    'lifetime' => 
    array (
      'en' => 'Lifetime',
      'sw' => 'Kipindi Chote',
    ),
    'live_data' => 
    array (
      'en' => 'Live Data',
      'sw' => 'Data ya Moja kwa Moja',
    ),
    'metrics_will_appear' => 
    array (
      'en' => 'Metrics will appear here once the system starts processing requests.',
      'sw' => 'Vipimo vitaonekana hapa mara tu mfumo unapoanza kuchakata maombi.',
    ),
    'month' => 
    array (
      'en' => 'Month',
      'sw' => 'Mwezi',
    ),
    'no_data' => 
    array (
      'en' => 'No data available',
      'sw' => 'Hakuna data inayopatikana',
    ),
    'normal' => 
    array (
      'en' => 'Normal',
      'sw' => 'Kawaida',
    ),
    'overdue' => 
    array (
      'en' => 'Overdue',
      'sw' => 'Zilizochelewa',
    ),
    'past_month' => 
    array (
      'en' => 'Past Month',
      'sw' => 'Mwezi Iliyopita',
    ),
    'past_week' => 
    array (
      'en' => 'Past Week',
      'sw' => 'Wiki Iliyopita',
    ),
    'past_year' => 
    array (
      'en' => 'Past Year',
      'sw' => 'Mwaka Iliyopita',
    ),
    'pending' => 
    array (
      'en' => 'Pending',
      'sw' => 'Inasubiri',
    ),
    'period' => 
    array (
      'en' => 'Period',
      'sw' => 'Kipindi',
    ),
    'preview' => 
    array (
      'en' => 'Preview',
      'sw' => 'Hakikisho',
    ),
    'priority' => 
    array (
      'en' => 'Priority',
      'sw' => 'Kipaumbele',
    ),
    'pro_tip' => 
    array (
      'en' => 'Pro-tip',
      'sw' => 'Kidokezo',
    ),
    'quota' => 
    array (
      'en' => 'Quota',
      'sw' => 'Upendeleo',
    ),
    'report' => 
    array (
      'en' => 'Report',
      'sw' => 'Ripoti',
    ),
    'search' => 
    array (
      'en' => 'Search',
      'sw' => 'Tafuta',
    ),
    'stable' => 
    array (
      'en' => 'Stable',
      'sw' => 'Imara',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'system_health' => 
    array (
      'en' => 'System Health',
      'sw' => 'Afya ya Mfumo',
    ),
    'throughput' => 
    array (
      'en' => 'Throughput',
      'sw' => 'Uzitishaji',
    ),
    'type' => 
    array (
      'en' => 'Type',
      'sw' => 'Aina',
    ),
    'urgent' => 
    array (
      'en' => 'Urgent',
      'sw' => 'Dharura',
    ),
    'verification_pending' => 
    array (
      'en' => 'Pending Verification',
      'sw' => 'Inasubiri Uhakiki',
    ),
    'view_all' => 
    array (
      'en' => 'View All',
      'sw' => 'Tazama Zote',
    ),
    'volume' => 
    array (
      'en' => 'Volume',
      'sw' => 'Kiasi',
    ),
    'warnings' => 
    array (
      'en' => 'Warnings',
      'sw' => 'Maonyo',
    ),
  ),
  'crm' => 
  array (
    'apply_filter' => 
    array (
      'en' => 'Apply Filter',
      'sw' => 'Tumia Kichujio',
    ),
    'client_name' => 
    array (
      'en' => 'Client Name',
      'sw' => 'Jina la Mteja',
    ),
    'label_1_month' => 
    array (
      'en' => 'Past Month',
      'sw' => 'Mwezi Iliopita',
    ),
    'label_1_week' => 
    array (
      'en' => 'Past Week',
      'sw' => 'Wiki Iliyopita',
    ),
    'label_2_months' => 
    array (
      'en' => 'Past 2 Months',
      'sw' => 'Miezi 2 Iliyopita',
    ),
    'label_2_weeks' => 
    array (
      'en' => 'Past 2 Weeks',
      'sw' => 'Wiki 2 Zilizopita',
    ),
    'label_annually' => 
    array (
      'en' => 'Past Year',
      'sw' => 'Mwaka Iliyopita',
    ),
    'label_custom' => 
    array (
      'en' => 'Custom Range',
      'sw' => 'Muda Maalum',
    ),
    'label_quarterly' => 
    array (
      'en' => 'Past Quarter',
      'sw' => 'Robo Mwaka Iliyopita',
    ),
    'label_semi_annually' => 
    array (
      'en' => 'Past 6 Months',
      'sw' => 'Miezi 6 Iliyopita',
    ),
    'order_trend_chart' => 
    array (
      'en' => 'Orders',
      'sw' => 'Maagizo',
    ),
    'order_trend_title' => 
    array (
      'en' => 'Order Trend',
      'sw' => 'Mwelekeo wa Agizo',
    ),
    'orders' => 
    array (
      'en' => 'Orders',
      'sw' => 'Maagizo',
    ),
    'period_1_month' => 
    array (
      'en' => '1 Month',
      'sw' => 'Mwezi 1',
    ),
    'period_1_week' => 
    array (
      'en' => '1 Week',
      'sw' => 'Wiki 1',
    ),
    'period_2_months' => 
    array (
      'en' => '2 Months',
      'sw' => 'Miezi 2',
    ),
    'period_2_weeks' => 
    array (
      'en' => '2 Weeks',
      'sw' => 'Wiki 2',
    ),
    'period_annually' => 
    array (
      'en' => 'Annual',
      'sw' => 'Kila Mwaka',
    ),
    'period_custom' => 
    array (
      'en' => 'Custom',
      'sw' => 'Maalum',
    ),
    'period_quarterly' => 
    array (
      'en' => 'Quarterly',
      'sw' => 'Robo Mwaka',
    ),
    'period_semi_annually' => 
    array (
      'en' => 'Semi-Annual',
      'sw' => 'Nusu Mwaka',
    ),
    'rank' => 
    array (
      'en' => 'Rank',
      'sw' => 'Nafasi',
    ),
    'subtitle' => 
    array (
      'en' => 'Customer order trends and key account performance.',
      'sw' => 'Mienendo ya agizo la mteja na utendaji wa akaunti kuu.',
    ),
    'title' => 
    array (
      'en' => 'CRM Analytics',
      'sw' => 'Uchambuzi wa CRM',
    ),
    'top_accounts' => 
    array (
      'en' => 'Top Accounts (Order Volume)',
      'sw' => 'Akaunti Kuu (Kiwango cha Agizo)',
    ),
    'total_batches' => 
    array (
      'en' => 'Total Batches',
      'sw' => 'Jumla ya Bachi',
    ),
    'workload_share' => 
    array (
      'en' => 'Workload Share',
      'sw' => 'Sehemu ya Kazi',
    ),
  ),
  'dashboard' => 
  array (
    'active_batches' => 
    array (
      'en' => 'Active Batches',
      'sw' => 'Bachi Zinazoendelea',
    ),
    'active_personnel' => 
    array (
      'en' => 'Active Personnel',
      'sw' => 'Watumishi Waliopo',
    ),
    'active_sample_types' => 
    array (
      'en' => 'Active Sample Types',
      'sw' => 'Aina za Sampuli Zinazoendelea',
    ),
    'active_workload' => 
    array (
      'en' => 'Active Workload',
      'sw' => 'Mzigo wa Kazi Amilifu',
    ),
    'aging_distribution' => 
    array (
      'en' => 'Aging Distribution (Batches)',
      'sw' => 'Usambazaji wa Muda (Bachi)',
    ),
    'ai_intelligence' => 
    array (
      'en' => 'AI Monitoring',
      'sw' => 'Akili Bandia (AI)',
    ),
    'asset_lifecycle' => 
    array (
      'en' => 'Asset Lifecycle',
      'sw' => 'Mzunguko wa Maisha ya Mali',
    ),
    'batch_overdue' => 
    array (
      'en' => 'Batch Overdue',
      'sw' => 'Bachi Zilizochelewa',
    ),
    'classification' => 
    array (
      'en' => 'Classification',
      'sw' => 'Uainishaji',
    ),
    'command_center' => 
    array (
      'en' => 'Laboratory Intelligence Command Center',
      'sw' => 'Kituo cha Amri cha Akili ya Maabara',
    ),
    'compliance_audit' => 
    array (
      'en' => 'Compliance Audit',
      'sw' => 'Ukaguzi wa Utekelezaji',
    ),
    'critical_threats' => 
    array (
      'en' => 'Critical Threats',
      'sw' => 'Vitisho Muhimu',
    ),
    'density_cluster' => 
    array (
      'en' => 'Density Cluster',
      'sw' => 'Nguzo ya Msongamano',
    ),
    'events_7d' => 
    array (
      'en' => 'Events (7d)',
      'sw' => 'Matukio (Siku 7)',
    ),
    'financials' => 
    array (
      'en' => 'Financials',
      'sw' => 'Fedha',
    ),
    'general_report' => 
    array (
      'en' => 'General Laboratory Analytics Report',
      'sw' => 'Ripoti ya Uchambuzi wa Maabara kwa Ujumla',
    ),
    'geographic_activity' => 
    array (
      'en' => 'Geographic Activity Points',
      'sw' => 'Pointi za Shughuli za Kijiografia',
    ),
    'human_capital' => 
    array (
      'en' => 'Human Capital',
      'sw' => 'Mtaji wa Watu',
    ),
    'insights_report' => 
    array (
      'en' => 'Laboratory Insights Report',
      'sw' => 'Ripoti ya Maarifa ya Maabara',
    ),
    'key_clients' => 
    array (
      'en' => 'Key Clients Engaged',
      'sw' => 'Wateja Muhimu Waliohusishwa',
    ),
    'lab_analytics' => 
    array (
      'en' => 'Lab Analytics',
      'sw' => 'Uchambuzi wa Maabara',
    ),
    'maint_overdue' => 
    array (
      'en' => 'Maint. Overdue',
      'sw' => 'Matengenezo Yaliyochelewa',
    ),
    'operational' => 
    array (
      'en' => 'OPERATIONAL',
      'sw' => 'UENDESHAJI',
    ),
    'operational_health' => 
    array (
      'en' => 'Operational Health Snapshot (Workflow)',
      'sw' => 'Snapshot ya Afya ya Uendeshaji (Workflow)',
    ),
    'operational_watchlist' => 
    array (
      'en' => 'Operational Watchlist (Smart Action Grid)',
      'sw' => 'Orodha ya Uangalizi wa Uendeshaji (Gridi ya Vitendo Mahiri)',
    ),
    'operations_summary' => 
    array (
      'en' => 'Laboratory Operations Summary',
      'sw' => 'Muhtasari wa Shughuli za Maabara',
    ),
    'pending_verification' => 
    array (
      'en' => 'Pending Verification',
      'sw' => 'Inasubiri Uhakiki',
    ),
    'pro_tip_text' => 
    array (
      'en' => 'Click on "Details" or use the sidebar to access deep-dive charts and visualizations for each module.',
      'sw' => 'Bofya kwenye "Maelezo" au tumia baraza ya pembeni kupata chati na picha za kina kwa kila moduli.',
    ),
    'qc_stability' => 
    array (
      'en' => 'QC & Stability',
      'sw' => 'QC na Uthabiti',
    ),
    'rank' => 
    array (
      'en' => 'Rank',
      'sw' => 'Cheo',
    ),
    'registration_trends' => 
    array (
      'en' => 'Registration Trends (Monthly)',
      'sw' => 'Mwenendo wa Usajili (Kila Mwezi)',
    ),
    'reorder_alerts' => 
    array (
      'en' => 'Reorder Alerts',
      'sw' => 'Arifa za Kuagiza Tena',
    ),
    'risk_profile' => 
    array (
      'en' => 'Risk Profile',
      'sw' => 'Wasifu wa Hatari',
    ),
    'stock_health' => 
    array (
      'en' => 'Stock Health',
      'sw' => 'Afya ya Akiba',
    ),
    'subtitle' => 
    array (
      'en' => 'High-level KPIs from across all modules.',
      'sw' => 'Viashiria vikuu vya utendaji kutoka moduli zote.',
    ),
    'system_health' => 
    array (
      'en' => 'System Health',
      'sw' => 'Afya ya Mfumo',
    ),
    'tat_performance' => 
    array (
      'en' => 'TAT Performance',
      'sw' => 'Utendaji wa TAT',
    ),
    'test_progress' => 
    array (
      'en' => 'Test Progress',
      'sw' => 'Maendeleo ya Vipimo',
    ),
    'tests_completed' => 
    array (
      'en' => 'Tests Completed',
      'sw' => 'Vipimo Vilivyokamilika',
    ),
    'title' => 
    array (
      'en' => 'Executive Overview',
      'sw' => 'Muhtasari wa Kiutendaji',
    ),
    'top_clients_share' => 
    array (
      'en' => 'Top Clients by Workload Share',
      'sw' => 'Wateja Bora kwa Sehemu ya Mzigo wa Kazi',
    ),
    'unpaid_invoices' => 
    array (
      'en' => 'Unpaid Invoices',
      'sw' => 'Ankara Zisizolipwa',
    ),
    'weight' => 
    array (
      'en' => 'Weight',
      'sw' => 'Uzito',
    ),
    'workload_distribution' => 
    array (
      'en' => 'Workload Distribution Analysis',
      'sw' => 'Uchambuzi wa Usambazaji wa Mzigo wa Kazi',
    ),
    'workload_percent' => 
    array (
      'en' => 'Workload %',
      'sw' => 'Asilimia ya Mzigo wa Kazi',
    ),
  ),
  'equipment' => 
  array (
    'active_assets' => 
    array (
      'en' => 'Active Assets',
      'sw' => 'Mali Amilifu',
    ),
    'avg_between_failures' => 
    array (
      'en' => 'Avg. between failures',
      'sw' => 'Wastani wa muda kati ya hitilafu',
    ),
    'compliance' => 
    array (
      'en' => 'Compliance',
      'sw' => 'Uzingatiaji',
    ),
    'critical_overdue' => 
    array (
      'en' => 'Critical Overdue Calibration',
      'sw' => 'Kalibresheni Muhimu Iliyochelewa',
    ),
    'currently_operational' => 
    array (
      'en' => 'Currently operational',
      'sw' => 'Inafanya kazi sasa',
    ),
    'days' => 
    array (
      'en' => 'days',
      'sw' => 'siku',
    ),
    'days_late' => 
    array (
      'en' => 'days late',
      'sw' => 'siku zilizochelewa',
    ),
    'equipment' => 
    array (
      'en' => 'Equipment',
      'sw' => 'Vifaa',
    ),
    'maintenance_due' => 
    array (
      'en' => 'Maintenance Due',
      'sw' => 'Urekebishaji Unaohitajika',
    ),
    'mtbf' => 
    array (
      'en' => 'MTBF (Hours)',
      'sw' => 'MTBF (Saa)',
    ),
    'next_7_days' => 
    array (
      'en' => 'Next 7 days',
      'sw' => 'Siku 7 zijazo',
    ),
    'overdue' => 
    array (
      'en' => 'Overdue',
      'sw' => 'Zilizochelewa',
    ),
    'reliability_score' => 
    array (
      'en' => 'Reliability Score',
      'sw' => 'Alama ya Uaminifu',
    ),
    'scheduled' => 
    array (
      'en' => 'Scheduled',
      'sw' => 'Imepangwa',
    ),
    'scheduled_date' => 
    array (
      'en' => 'Scheduled Date',
      'sw' => 'Tarehe Iliyopangwa',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'subtitle' => 
    array (
      'en' => 'Asset health, calibration compliance, and downtime analysis.',
      'sw' => 'Afya ya mali, uzingatiaji wa kurekebisha, na uchambuzi wa muda wa kupumzika.',
    ),
    'title' => 
    array (
      'en' => 'Equipment Maintenance & Reliability',
      'sw' => 'Urekebishaji na Uaminifu wa Vifaa',
    ),
    'type' => 
    array (
      'en' => 'Type',
      'sw' => 'Aina',
    ),
    'upcoming_schedule' => 
    array (
      'en' => 'Upcoming Maintenance Schedule',
      'sw' => 'Ratiba ya Urekebishaji Inayokuja',
    ),
    'urgency' => 
    array (
      'en' => 'Urgency',
      'sw' => 'Uharaka',
    ),
    'urgent' => 
    array (
      'en' => 'Urgent',
      'sw' => 'Haraka',
    ),
  ),
  'inventory' => 
  array (
    'available' => 
    array (
      'en' => 'Available',
      'sw' => 'Inapatikana',
    ),
    'below_minimum' => 
    array (
      'en' => 'Below Minimum',
      'sw' => 'Chini ya Kiwango cha Chini',
    ),
    'critical' => 
    array (
      'en' => 'CRITICAL',
      'sw' => 'HATARI',
    ),
    'expiry_risk' => 
    array (
      'en' => 'EXPIRY RISK',
      'sw' => 'HATARI YA KWISHA MUDA',
    ),
    'healthy' => 
    array (
      'en' => 'HEALTHY',
      'sw' => 'SALAMA',
    ),
    'high_risk_analysis' => 
    array (
      'en' => 'High-Risk Stock Analysis (Top 10)',
      'sw' => 'Uchambuzi wa Akiba Yenye Hatari Kubwa (Top 10)',
    ),
    'item_name' => 
    array (
      'en' => 'Item Name',
      'sw' => 'Jina la Kitu',
    ),
    'min_level' => 
    array (
      'en' => 'Min Level',
      'sw' => 'Kiwango cha Chini',
    ),
    'near_expiry' => 
    array (
      'en' => 'Near Expiry',
      'sw' => 'Karibu na Muda wa Kuisha',
    ),
    'open_inventory_module' => 
    array (
      'en' => 'Open Inventory Module',
      'sw' => 'Fungua Moduli ya Akiba',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'store' => 
    array (
      'en' => 'Store',
      'sw' => 'Stoo',
    ),
    'subtitle' => 
    array (
      'en' => 'Stock availability, reorder tracking, and sub-category analysis.',
      'sw' => 'Upatikanaji wa akiba, ufuatiliaji wa kuagiza tena, na uchambuzi wa makundi madogo.',
    ),
    'title' => 
    array (
      'en' => 'Inventory Health',
      'sw' => 'Afya ya Akiba',
    ),
    'tracked_items' => 
    array (
      'en' => 'Tracked Items',
      'sw' => 'Vitu Vinavyofuatiliwa',
    ),
  ),
  'lab' => 
  array (
    'action' => 
    array (
      'en' => 'Action',
      'sw' => 'Hatua',
    ),
    'active' => 
    array (
      'en' => 'Active',
      'sw' => 'Amilifu',
    ),
    'active_batches_col' => 
    array (
      'en' => 'Active Batches',
      'sw' => 'Bachi Amilifu',
    ),
    'active_batches_subtitle' => 
    array (
      'en' => 'Active Batches in Lab',
      'sw' => 'Bachi Amilifu Lab',
    ),
    'active_clients' => 
    array (
      'en' => 'Active Clients',
      'sw' => 'Wateja Waliopo',
    ),
    'active_workload' => 
    array (
      'en' => 'Active Workload',
      'sw' => 'Mzigo wa Kazi Amilifu',
    ),
    'actual' => 
    array (
      'en' => 'Actual',
    ),
    'aging_health' => 
    array (
      'en' => 'Aging Health Distribution',
      'sw' => 'Usambazaji wa Afya ya Umri',
    ),
    'all_clear' => 
    array (
      'en' => 'All items cleared in this category.',
      'sw' => 'Vitu vyote vimekamilika katika jamii hii.',
    ),
    'analyst' => 
    array (
      'en' => 'Analyst',
    ),
    'analyst_performance' => 
    array (
      'en' => 'Analyst Performance',
    ),
    'analyst_performance_note' => 
    array (
      'en' => 'Performance is based on average TAT offset and total volume of completed analytes.',
    ),
    'approvals' => 
    array (
      'en' => 'Approvals',
      'sw' => 'Idhini',
    ),
    'avg_days_tat' => 
    array (
      'en' => 'Avg. Days (TAT)',
      'sw' => 'Wastani wa Siku (TAT)',
    ),
    'avg_tat' => 
    array (
      'en' => 'Avg. TAT',
    ),
    'batch_code' => 
    array (
      'en' => 'Batch Code',
      'sw' => 'Kificho cha Bachi',
    ),
    'batch_count' => 
    array (
      'en' => 'Batch Count',
      'sw' => 'Jumla ya Bachi',
    ),
    'batch_sla_performance' => 
    array (
      'en' => 'Batch SLA Performance',
      'sw' => 'Utendaji wa SLA ya Bachi',
    ),
    'batches_within_target' => 
    array (
      'en' => ':percent% of samples met their Target Date',
      'sw' => ':percent% ya bachi ziko ndani ya lengo',
    ),
    'bucket_1_3_overdue' => 
    array (
      'en' => '1-3 days overdue',
      'sw' => 'Siku 1-3 Zilizochelewa',
    ),
    'bucket_4_7_overdue' => 
    array (
      'en' => '4-7 days overdue',
      'sw' => 'Siku 4-7 Zilizochelewa',
    ),
    'bucket_8_plus_overdue' => 
    array (
      'en' => '8+ days overdue',
      'sw' => 'Siku 8+ Zilizochelewa',
    ),
    'bucket_due_today' => 
    array (
      'en' => 'Due today',
      'sw' => 'Inatakiwa Leo',
    ),
    'bucket_no_target' => 
    array (
      'en' => 'No target date',
      'sw' => 'Bila Tarehe Lengwa',
    ),
    'bucket_on_time' => 
    array (
      'en' => 'On time',
      'sw' => 'Kwa Wakati',
    ),
    'client' => 
    array (
      'en' => 'Client',
      'sw' => 'Mteja',
    ),
    'client_name' => 
    array (
      'en' => 'Client Name',
      'sw' => 'Jina la Mteja',
    ),
    'cockpit_subtitle' => 
    array (
      'en' => 'Direct actions for urgent and pending items.',
      'sw' => 'Hatua za moja kwa moja kwa vitu vya dharura na vinavyosubiri.',
    ),
    'cockpit_title' => 
    array (
      'en' => 'Operational Cockpit',
      'sw' => 'Chumba cha Maelekezo',
    ),
    'completed' => 
    array (
      'en' => 'Completed',
      'sw' => 'Zimekamilika',
    ),
    'completion_ratio' => 
    array (
      'en' => 'Test Completion Ratio',
      'sw' => 'Uwiano wa Ukamilishaji wa Vipimo',
    ),
    'days_overdue' => 
    array (
      'en' => 'Days Overdue',
      'sw' => 'Siku Zilizochelewa',
    ),
    'delayed' => 
    array (
      'en' => 'Days Delayed',
    ),
    'detailed_insights' => 
    array (
      'en' => 'Detailed Analyte TAT Insights',
    ),
    'download_report' => 
    array (
      'en' => 'Download Report',
      'sw' => 'Pakua Ripoti',
    ),
    'early' => 
    array (
      'en' => 'Days Early',
    ),
    'efficiency_monitoring' => 
    array (
      'en' => 'Efficiency Monitoring',
      'sw' => 'Usimamizi wa Ufanisi',
    ),
    'engaged_current_period' => 
    array (
      'en' => 'Engaged in Current Period',
      'sw' => 'Waliohusika katika Kipindi cha Sasa',
    ),
    'expected' => 
    array (
      'en' => 'Expected',
    ),
    'general_subtitle' => 
    array (
      'en' => 'Overview of workload distribution and customer engagement.',
      'sw' => 'Muhtasari wa usambazaji wa kazi na ushiriki wa wateja.',
    ),
    'general_title' => 
    array (
      'en' => 'Laboratory General Analytics',
      'sw' => 'Uchambuzi wa Jumla wa Maabara',
    ),
    'geographic_density' => 
    array (
      'en' => 'Geographic Sample Density',
      'sw' => 'Uzito wa Sampuli za Kijiografia',
    ),
    'high_risk_items' => 
    array (
      'en' => 'Top :count High Risk Items',
      'sw' => 'Vitu :count vya Hatari Kubwa',
    ),
    'historical_section_tat' => 
    array (
      'en' => 'Historical Section TAT (Days)',
    ),
    'historical_trends' => 
    array (
      'en' => 'Historical Registration Trends',
      'sw' => 'Mwenendo wa Usajili wa Kihistoria',
    ),
    'lab_section' => 
    array (
      'en' => 'Laboratory Section',
    ),
    'last_period' => 
    array (
      'en' => 'Last :period',
      'sw' => ':period Iliyopita',
    ),
    'lifetime' => 
    array (
      'en' => 'Lifetime',
      'sw' => 'Maisha Yote',
    ),
    'lifetime_total' => 
    array (
      'en' => 'Lifetime Total',
      'sw' => 'Jumla ya Maisha',
    ),
    'live_data' => 
    array (
      'en' => 'Live Data',
      'sw' => 'Data ya Moja kwa Moja',
    ),
    'month' => 
    array (
      'en' => 'Month',
      'sw' => 'Mwezi',
    ),
    'my_tasks' => 
    array (
      'en' => 'My Tasks',
      'sw' => 'Kazi Zangu',
    ),
    'no_overdue_batches' => 
    array (
      'en' => 'No critical overdue batches detected.',
      'sw' => 'Hakuna bachi zilizochelewa sana zilizopatikana.',
    ),
    'offset' => 
    array (
      'en' => 'Offset',
    ),
    'overdue' => 
    array (
      'en' => 'Overdue',
      'sw' => 'Zimechelewa',
    ),
    'overdue_batches' => 
    array (
      'en' => ':count Overdue',
      'sw' => ':count Bachi Zimechelewa',
    ),
    'overdue_count' => 
    array (
      'en' => ':count Overdue',
      'sw' => ':count Zimechelewa',
    ),
    'overdue_watchlist' => 
    array (
      'en' => 'Critical Overdue Watchlist',
      'sw' => 'Orodha ya Bachi Zilizochelewa Sana',
    ),
    'past_month' => 
    array (
      'en' => 'Past Month',
      'sw' => 'Mwezi Iliopita',
    ),
    'past_week' => 
    array (
      'en' => 'Past Week',
      'sw' => 'Wiki Iliyopita',
    ),
    'past_year' => 
    array (
      'en' => 'Past Year',
      'sw' => 'Mwaka Iliopita',
    ),
    'pending' => 
    array (
      'en' => 'Pending',
      'sw' => 'Inasubiri',
    ),
    'performance_leaderboard' => 
    array (
      'en' => 'Performance Leaderboard',
    ),
    'period_active_workload' => 
    array (
      'en' => 'Active Workload',
      'sw' => 'Kazi Amilifu',
    ),
    'preview' => 
    array (
      'en' => 'Preview',
      'sw' => 'Angalia Kwanza',
    ),
    'priority' => 
    array (
      'en' => 'Priority',
      'sw' => 'Kipaumbele',
    ),
    'rank' => 
    array (
      'en' => 'Rank',
      'sw' => 'Nafasi',
    ),
    'received' => 
    array (
      'en' => 'Received',
    ),
    'risk_level' => 
    array (
      'en' => 'Risk Level',
      'sw' => 'Ngazi ya Hatari',
    ),
    'sample_type' => 
    array (
      'en' => 'Sample Type',
      'sw' => 'Aina ya Sampuli',
    ),
    'sample_type_distribution' => 
    array (
      'en' => 'Sample Type Distribution',
      'sw' => 'Usambazaji wa Aina ya Sampuli',
    ),
    'section_performance' => 
    array (
      'en' => 'Lab Section Technical Performance',
    ),
    'section_performance_subtitle' => 
    array (
      'en' => 'Deep-dive into turnaround times and workload across different laboratory sections.',
    ),
    'sla_compliance_rate' => 
    array (
      'en' => 'SLA Compliance Rate',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'status_completed' => 
    array (
      'en' => 'Completed',
      'sw' => 'Zimekamilika',
    ),
    'status_finished_sample' => 
    array (
      'en' => 'Finished Sample',
      'sw' => 'Sampuli Imekamilika',
    ),
    'status_sample_approval' => 
    array (
      'en' => 'Sample Approval',
      'sw' => 'Idhini ya Sampuli',
    ),
    'status_sample_logged' => 
    array (
      'en' => 'Sample Logged',
      'sw' => 'Sampuli Imerekodiwa',
    ),
    'status_sample_registration' => 
    array (
      'en' => 'Sample Registration',
      'sw' => 'Usajili wa Sampuli',
    ),
    'status_sample_verification' => 
    array (
      'en' => 'Sample Verification',
      'sw' => 'Uhakiki wa Sampuli',
    ),
    'status_samples_in_lab' => 
    array (
      'en' => 'Samples In Lab',
      'sw' => 'Sampuli Ziko Lab',
    ),
    'target_date' => 
    array (
      'en' => 'Target Date',
      'sw' => 'Tarehe Lengwa',
    ),
    'target_distribution' => 
    array (
      'en' => 'Target Distribution',
      'sw' => 'Usambazaji Lengwa',
    ),
    'tat_subtitle' => 
    array (
      'en' => 'Turnaround time monitoring and SLA compliance tracking.',
      'sw' => 'Usimamizi wa muda wa kukamilisha na ufuatiliaji wa uzingatiaji wa SLA.',
    ),
    'tat_title' => 
    array (
      'en' => 'Laboratory TAT Analysis',
      'sw' => 'Uchambuzi wa TAT ya Maabara',
    ),
    'tests' => 
    array (
      'en' => 'Tests',
    ),
    'tests_completed' => 
    array (
      'en' => 'Tests Completed',
      'sw' => 'Vipimo Vilivyokamilika',
    ),
    'tests_processed' => 
    array (
      'en' => 'Tests Processed',
      'sw' => 'Vipimo Vilivyochakatwa',
    ),
    'throughput_volume' => 
    array (
      'en' => 'Throughput Volume (Analytes)',
      'sw' => 'Kiwango cha Uzalishaji (Vichambuzi)',
    ),
    'top_analysts' => 
    array (
      'en' => 'Top Performing Analysts',
    ),
    'top_clients_ranking' => 
    array (
      'en' => 'Top Clients Ranking',
      'sw' => 'Nafasi za Wateja Wakuu',
    ),
    'total_requested' => 
    array (
      'en' => 'Total Requested',
      'sw' => 'Jumla Yaliyoitishwa',
    ),
    'try_other_periods' => 
    array (
      'en' => 'Try selecting a different period (e.g., Past Month or Lifetime) to see more historical data.',
    ),
    'urgent' => 
    array (
      'en' => 'Urgent',
      'sw' => 'Dharura',
    ),
    'volume' => 
    array (
      'en' => 'Volume',
    ),
    'volume_top_clients' => 
    array (
      'en' => 'Volume by Top Clients',
      'sw' => 'Kiwango kwa Wateja Wakuu',
    ),
    'week' => 
    array (
      'en' => 'Week',
      'sw' => 'Wiki',
    ),
    'workflow_distribution' => 
    array (
      'en' => 'Workflow Stage Distribution',
      'sw' => 'Usambazaji wa Hatua za Kazi',
    ),
    'workflow_stage' => 
    array (
      'en' => 'Workflow Stage',
      'sw' => 'Hatua ya Kazi',
    ),
    'workflow_stages' => 
    array (
      'en' => 'Laboratory Workflow Stages',
      'sw' => 'Hatua za Kazi za Maabara',
    ),
    'workload_occupancy' => 
    array (
      'en' => 'Workload Occupancy',
    ),
    'workload_share' => 
    array (
      'en' => 'Workload Share',
      'sw' => 'Sehemu ya Kiwango cha Kazi',
    ),
    'workload_volume' => 
    array (
      'en' => 'Workload Volume',
      'sw' => 'Kiwango cha Kazi',
    ),
    'year' => 
    array (
      'en' => 'Year',
      'sw' => 'Mwaka',
    ),
  ),
  'logistics' => 
  array (
    'active_buffers' => 
    array (
      'en' => 'Active Buffers',
      'sw' => 'Bafa Amilifu',
    ),
    'active_solutions' => 
    array (
      'en' => 'Active standard solutions & reagents',
      'sw' => 'Mchanganyiko wa kawaida na vitendanishi amilifu',
    ),
    'ai_insights' => 
    array (
      'en' => 'AI Procurement Insights',
      'sw' => 'Insights za Ununuzi wa AI',
    ),
    'ai_suggestion_text' => 
    array (
      'en' => 'AI suggests ordering <strong>Buffer A</strong> and <strong>Reagent X</strong> based on the last 30 days of consumption trends.',
      'sw' => 'AI inapendekeza kuagiza <strong>Bafa A</strong> na <strong>Kitendanishi X</strong> kulingana na mienendo ya matumizi ya siku 30 zilizopita.',
    ),
    'audit_title' => 
    array (
      'en' => 'Logistics & Supplies Audit',
      'sw' => 'Ukaguzi wa Logistiki na Ugavi',
    ),
    'available_qty' => 
    array (
      'en' => 'Available Qty',
      'sw' => 'Kiasi Kilichopo',
    ),
    'avg_lead_time' => 
    array (
      'en' => 'Average lead time compliance',
      'sw' => 'Wastani wa uzingatiaji wa muda wa kuwasilisha',
    ),
    'batch_code' => 
    array (
      'en' => 'Batch/Code',
      'sw' => 'Bachi/Kodi',
    ),
    'below_threshold' => 
    array (
      'en' => 'Items below minimum threshold',
      'sw' => 'Bidhaa zilizo chini ya kiwango cha chini',
    ),
    'buffer_consumable' => 
    array (
      'en' => 'Buffer/Consumable',
      'sw' => 'Bafa/Zinazotumiwa',
    ),
    'consumable_pulse' => 
    array (
      'en' => 'Consumable Pulse',
      'sw' => 'Pulse ya Zinazotumiwa',
    ),
    'consumption' => 
    array (
      'en' => 'Consumption',
      'sw' => 'Matumizi',
    ),
    'critical_depletion' => 
    array (
      'en' => 'Critical stock depletion detected for :count items. Recommended immediate restock to avoid testing delays.',
      'sw' => 'Upungufu mkubwa wa akiba umegunduliwa kwa bidhaa :count. Inapendekezwa kujaza akiba mara moja ili kuepuka ucheleweshaji wa vipimo.',
    ),
    'critically_low' => 
    array (
      'en' => 'Critically Low',
      'sw' => 'Chini Sana',
    ),
    'current_qty' => 
    array (
      'en' => 'Current Qty',
      'sw' => 'Kiasi cha Sasa',
    ),
    'download_audit' => 
    array (
      'en' => 'Download Audit',
      'sw' => 'Pakua Ukaguzi',
    ),
    'generate_requisition' => 
    array (
      'en' => 'Generate Requisition',
      'sw' => 'Tengeneza Ombi la Vifaa',
    ),
    'health' => 
    array (
      'en' => 'Health',
      'sw' => 'Afya',
    ),
    'health_scorecard' => 
    array (
      'en' => 'Logistics Health Scorecard',
      'sw' => 'Kadi ya Alama ya Afya ya Logistiki',
    ),
    'high' => 
    array (
      'en' => 'High',
      'sw' => 'Juu',
    ),
    'inventory_title' => 
    array (
      'en' => 'Buffer & Reagent Inventory',
      'sw' => 'Vifaa vya Bafa na Vitendanishi',
    ),
    'item_description' => 
    array (
      'en' => 'Item Description',
      'sw' => 'Maelezo ya Bidhaa',
    ),
    'item_name' => 
    array (
      'en' => 'Item Name',
      'sw' => 'Jina la Bidhaa',
    ),
    'low_stock' => 
    array (
      'en' => 'LOW STOCK',
      'sw' => 'AKIBA YA CHINI',
    ),
    'low_stock_alerts' => 
    array (
      'en' => 'Low Stock Alerts',
      'sw' => 'Tahadhari za Akiba ya Chini',
    ),
    'min_level' => 
    array (
      'en' => 'Min Level',
      'sw' => 'Kiwango cha Chini',
    ),
    'minimal' => 
    array (
      'en' => 'Minimal',
      'sw' => 'Kidogo sana',
    ),
    'monitored_buffers' => 
    array (
      'en' => 'Monitored Buffers',
      'sw' => 'Bafa Zinazofuatiliwa',
    ),
    'monitoring' => 
    array (
      'en' => 'Laboratory Logistics Monitoring',
      'sw' => 'Ufuatiliaji wa Logistiki ya Maabara',
    ),
    'monthly_movements' => 
    array (
      'en' => 'Monthly Movements',
      'sw' => 'Mizunguko ya Kila Mwezi',
    ),
    'movements_30d' => 
    array (
      'en' => 'Recent Consumption Movements (30 Days)',
      'sw' => 'Mizunguko ya Matumizi ya Hivi Karibuni (Siku 30)',
    ),
    'no_movements' => 
    array (
      'en' => 'No recent movements recorded.',
      'sw' => 'Hakuna mzunguko wa hivi karibuni uliorekodiwa.',
    ),
    'operator' => 
    array (
      'en' => 'Operator',
      'sw' => 'Mwendeshaji',
    ),
    'optimal' => 
    array (
      'en' => 'OPTIMAL',
      'sw' => 'BORA',
    ),
    'optimum' => 
    array (
      'en' => 'Optimum',
      'sw' => 'Bora/Inatosha',
    ),
    'prep_frequency' => 
    array (
      'en' => 'Prep Frequency',
      'sw' => 'Mzunguko wa Maandalizi',
    ),
    'preview' => 
    array (
      'en' => 'Preview',
      'sw' => 'Onyesho la Awali',
    ),
    'procurement_insight' => 
    array (
      'en' => 'Procurement Insight',
      'sw' => 'Mtizamo wa Ununuzi',
    ),
    'recent_movements_title' => 
    array (
      'en' => 'Recent Consumption & Prep Records',
      'sw' => 'Rekodi za Hivi Karibuni za Matumizi na Maandalizi',
    ),
    'refresh_data' => 
    array (
      'en' => 'Refresh Data',
      'sw' => 'Sasaisha Data',
    ),
    'report_title' => 
    array (
      'en' => 'Lab Logistics & Supplies Report',
      'sw' => 'Ripoti ya Logistiki na Ugavi wa Maabara',
    ),
    'restock_compliance' => 
    array (
      'en' => 'Restock Compliance',
      'sw' => 'Uzingatiaji wa Kurejesha Akiba',
    ),
    'stable_supply' => 
    array (
      'en' => 'Supply chain levels are stable. Consumption rates match historical patterns for the current volume.',
      'sw' => 'Viwango vya ugavi viko thabiti. Viwango vya matumizi vinalingana na mifumo ya kihistoria kwa kiasi cha sasa.',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'status_title' => 
    array (
      'en' => 'Buffer Stock Status',
      'sw' => 'Hali ya Akiba ya Bafa',
    ),
    'subtitle' => 
    array (
      'en' => 'Analytics for buffer stocks, reagents, and consumable movement.',
      'sw' => 'Uchambuzi wa akiba za bafa, vitendanishi, na mzunguko wa bidhaa zinazotumiwa.',
    ),
    'timestamp' => 
    array (
      'en' => 'Timestamp',
      'sw' => 'Muda/Tarehe',
    ),
    'title' => 
    array (
      'en' => 'Laboratory Logistics & Supplies',
      'sw' => 'Logistiki na Ugavi wa Maabara',
    ),
    'units' => 
    array (
      'en' => 'Units',
      'sw' => 'Vitengo',
    ),
    'waste_factor' => 
    array (
      'en' => 'Waste Factor',
      'sw' => 'Sababu ya Upotevu',
    ),
  ),
  'navigation' => 
  array (
    'ai_analytics' => 
    array (
      'en' => 'AI Monitoring',
      'sw' => 'Uchambuzi wa AI',
    ),
    'ai_intelligence' => 
    array (
      'en' => 'AI Intelligence',
      'sw' => 'Akili ya AI',
    ),
    'analytical_suite' => 
    array (
      'en' => 'Analytical Suite',
      'sw' => 'Suti ya Uchambuzi',
    ),
    'audit_log' => 
    array (
      'en' => 'Audit Log',
      'sw' => 'Kumbukumbu ya Ukaguzi',
    ),
    'crm_analytics' => 
    array (
      'en' => 'CRM Analytics',
      'sw' => 'Uchambuzi wa CRM',
    ),
    'equipment' => 
    array (
      'en' => 'Equipment',
      'sw' => 'Vifaa',
    ),
    'general_analytics' => 
    array (
      'en' => 'General Analytics',
      'sw' => 'Uchambuzi wa Jumla',
    ),
    'global_overview' => 
    array (
      'en' => 'Global Overview',
      'sw' => 'Muhtasari wa Jumla',
    ),
    'imara_lims' => 
    array (
      'en' => 'Imara LIMS',
      'sw' => 'Imara LIMS',
    ),
    'inventory_health' => 
    array (
      'en' => 'Inventory Health',
      'sw' => 'Afya ya Akiba',
    ),
    'lab_insights' => 
    array (
      'en' => 'Lab Insights',
      'sw' => 'Ufahamu wa Maabara',
    ),
    'logistics_supplies' => 
    array (
      'en' => 'Logistics & Supplies',
      'sw' => 'Vifaa na Ugavi',
    ),
    'management_section' => 
    array (
      'en' => 'Management Section',
      'sw' => 'Sehemu ya Usimamizi',
    ),
    'personnel' => 
    array (
      'en' => 'Personnel',
      'sw' => 'Wafanyakazi',
    ),
    'qc_analytics' => 
    array (
      'en' => 'QC Analytics',
      'sw' => 'Uchambuzi wa QC',
    ),
    'quality_control' => 
    array (
      'en' => 'Quality Control',
      'sw' => 'Udhibiti wa Ubora',
    ),
    'risk_metrics' => 
    array (
      'en' => 'Risk Metrics',
      'sw' => 'Vipimo vya Hatari',
    ),
    'tat_analysis' => 
    array (
      'en' => 'TAT Analysis',
      'sw' => 'Uchambuzi wa TAT',
    ),
  ),
  'personnel' => 
  array (
    'active_staff' => 
    array (
      'en' => 'Active Staff',
      'sw' => 'Wafanyakazi Amilifu',
    ),
    'certifications' => 
    array (
      'en' => 'Certifications',
      'sw' => 'Vyeti',
    ),
    'departments' => 
    array (
      'en' => 'Departments',
      'sw' => 'Idara',
    ),
    'details_intro' => 
    array (
      'en' => 'Manage profile, employment, and access information in one place.',
      'sw' => 'Simamia taarifa za wasifu, ajira, na ufikiaji katika sehemu moja.',
    ),
    'manage_all' => 
    array (
      'en' => 'Manage All Personnel',
      'sw' => 'Simamia Wafanyakazi Wote',
    ),
    'org_deep_dive' => 
    array (
      'en' => 'Organizational Deep-Dive',
      'sw' => 'Uchunguzi wa Kina wa Shirika',
    ),
    'org_description' => 
    array (
      'en' => 'The Personnel module provides exhaustive tracking of analysts, supervisors, and administrative staff. Detailed individual metrics, work history, and skills matrix can be accessed through the dedicated Personnel Section.',
      'sw' => 'Moduli ya Wafanyakazi hutoa ufuatiliaji wa kina wa wachambuzi, wasimamizi, na wafanyakazi wa utawala. Vipimo vya kina vya mtu binafsi, historia ya kazi, na matrix ya ujuzi vinaweza kupatikana kupitia Sehemu maalum ya Wafanyakazi.',
    ),
    'section_access_assignment' => 
    array (
      'en' => 'Access and Lab Assignment',
      'sw' => 'Ufikiaji na Mgawanyo wa Maabara',
    ),
    'section_access_assignment_hint' => 
    array (
      'en' => 'License controls and section-level permissions.',
      'sw' => 'Udhibiti wa leseni na ruhusa za kiwango cha sehemu.',
    ),
    'section_employment_details' => 
    array (
      'en' => 'Employment Details',
      'sw' => 'Taarifa za Ajira',
    ),
    'section_employment_details_hint' => 
    array (
      'en' => 'Role, department, and workplace assignment.',
      'sw' => 'Wadhifa, idara, na mgawanyo wa mahali pa kazi.',
    ),
    'section_personal_information' => 
    array (
      'en' => 'Personal Information',
      'sw' => 'Taarifa Binafsi',
    ),
    'section_personal_information_hint' => 
    array (
      'en' => 'Identity and contact details.',
      'sw' => 'Utambulisho na taarifa za mawasiliano.',
    ),
    'subtitle' => 
    array (
      'en' => 'Staff distribution, active certifications, and organizational departments.',
      'sw' => 'Usambazaji wa wafanyakazi, vyeti amilifu, na idara za shirika.',
    ),
    'title' => 
    array (
      'en' => 'Personnel & Human Capital',
      'sw' => 'Wafanyakazi na Mtaji wa Watu',
    ),
    'total_users' => 
    array (
      'en' => 'Total Users',
      'sw' => 'Jumla ya Watumiaji',
    ),
  ),
  'qc' => 
  array (
    'analyte_name' => 
    array (
      'en' => 'Analyte Name',
      'sw' => 'Jina la Analiti',
    ),
    'analytes' => 
    array (
      'en' => 'Analytes',
      'sw' => 'Analiti',
    ),
    'avg_cv' => 
    array (
      'en' => 'Avg. Robust CV%',
      'sw' => 'Wastani wa Robust CV%',
    ),
    'avg_cv_pct' => 
    array (
      'en' => 'Avg CV%',
      'sw' => 'Wastani wa CV%',
    ),
    'compliance' => 
    array (
      'en' => 'COMPLIANCE',
      'sw' => 'UZINGATIAJI',
    ),
    'critical' => 
    array (
      'en' => 'Critical',
      'sw' => 'Muhimu',
    ),
    'critical_exceptions' => 
    array (
      'en' => 'Critical Exceptions',
      'sw' => 'Vighairi Muhimu',
    ),
    'cv_threshold' => 
    array (
      'en' => 'Within 5% CV threshold',
      'sw' => 'Ndani ya kizingiti cha 5% CV',
    ),
    'data_unavailable' => 
    array (
      'en' => 'QC stability data is currently unavailable.',
      'sw' => 'Data ya uthabiti wa QC haipatikani kwa sasa.',
    ),
    'distribution_title' => 
    array (
      'en' => 'Stability Status Distribution',
      'sw' => 'Usambazaji wa Hali ya Uthabiti',
    ),
    'leaderboard_title' => 
    array (
      'en' => 'Method Performance Leaderboard',
      'sw' => 'Bao la Utendaji wa Njia',
    ),
    'method_analyte' => 
    array (
      'en' => 'Method/Analyte',
      'sw' => 'Njia/Analiti',
    ),
    'monitored_params' => 
    array (
      'en' => 'Monitored parameters',
      'sw' => 'Vigezo vinavyofuatiliwa',
    ),
    'no_exceptions' => 
    array (
      'en' => 'No QC exceptions detected. Systems are stable.',
      'sw' => 'Hakuna vighairi vya QC vilivyogunduliwa. Mifumo iko thabiti.',
    ),
    'oversight' => 
    array (
      'en' => 'Quality Control Laboratory Oversight',
      'sw' => 'Usimamizi wa Maabara ya Udhibiti wa Ubora',
    ),
    'pass_rate' => 
    array (
      'en' => 'Pass Rate',
      'sw' => 'Kiwango cha Kufaulu',
    ),
    'preview' => 
    array (
      'en' => 'Preview',
      'sw' => 'Hakikisho',
    ),
    'recalibration_required' => 
    array (
      'en' => 'Requires immediate recalibration',
      'sw' => 'Inahitaji urekebishaji wa haraka',
    ),
    'report_title' => 
    array (
      'en' => 'QC Stability Performance Report',
      'sw' => 'Ripoti ya Utendaji wa Uthabiti wa QC',
    ),
    'robust_cv' => 
    array (
      'en' => 'Robust CV%',
      'sw' => 'Robust CV%',
    ),
    'sample_type' => 
    array (
      'en' => 'Sample Type',
      'sw' => 'Aina ya Sampuli',
    ),
    'share' => 
    array (
      'en' => 'Share',
      'sw' => 'Sehemu',
    ),
    'snapshot_title' => 
    array (
      'en' => 'QC Stability & Distribution Snapshot',
      'sw' => 'Snapshot ya Uthabiti na Usambazaji wa QC',
    ),
    'stable' => 
    array (
      'en' => 'Stable',
      'sw' => 'Thabiti',
    ),
    'stable_controls' => 
    array (
      'en' => 'Stable Controls',
      'sw' => 'Vidhibiti Thabiti',
    ),
    'status' => 
    array (
      'en' => 'Status',
      'sw' => 'Hali',
    ),
    'subtitle' => 
    array (
      'en' => 'Quality Control performance monitoring and stability tracking.',
      'sw' => 'Ufuatiliaji wa utendaji wa Udhibiti wa Ubora na ufuatiliaji wa uthabiti.',
    ),
    'summary_scorecard' => 
    array (
      'en' => 'Stability Summary Scorecard',
      'sw' => 'Kadi ya Alama ya Muhtasari wa Uthabiti',
    ),
    'system_precision' => 
    array (
      'en' => 'System-wide precision',
      'sw' => 'Usahihi wa mfumo mzima',
    ),
    'testing_matrix' => 
    array (
      'en' => 'Testing Matrix (Sample Type vs Section)',
      'sw' => 'Testing Matrix (Aina ya Sampuli dhidi ya Sehemu)',
    ),
    'title' => 
    array (
      'en' => 'QC Stability Analytics',
      'sw' => 'Uchambuzi wa Uthabiti wa QC',
    ),
    'top_10' => 
    array (
      'en' => 'Top 10 by Volume',
      'sw' => '10 Bora kwa Kiasi',
    ),
    'top_exceptions_title' => 
    array (
      'en' => 'High Variation Analytes (CV%)',
      'sw' => 'Analiti zenye Tofauti Kubwa (CV%)',
    ),
    'total_analytes' => 
    array (
      'en' => 'Total QC Analytes',
      'sw' => 'Jumla ya Analiti za QC',
    ),
    'total_tests' => 
    array (
      'en' => 'Total Tests',
      'sw' => 'Jumla ya Vipimo',
    ),
    'trend' => 
    array (
      'en' => 'Trend',
      'sw' => 'Mwenendo',
    ),
    'unknown' => 
    array (
      'en' => 'Unknown',
      'sw' => 'Haijulikani',
    ),
    'warning' => 
    array (
      'en' => 'Warning',
      'sw' => 'Onyo',
    ),
    'watchlist_prioritized' => 
    array (
      'en' => 'Prioritized by Variation Level',
      'sw' => 'Imepewa kipaumbele kwa Ngazi ya Tofauti',
    ),
    'watchlist_title' => 
    array (
      'en' => 'Stability Watchlist',
      'sw' => 'Orodha ya Ufuatiliaji wa Uthabiti',
    ),
    'workload_matrix' => 
    array (
      'en' => 'Workload Matrix (%)',
      'sw' => 'Matrix ya Jaribio (Sample Type vs Section)',
    ),
  ),
  'report' => 
  array (
    'classification' => 
    array (
      'en' => 'Classification',
      'sw' => 'Uainishaji',
    ),
    'confidential' => 
    array (
      'en' => 'Confidential',
      'sw' => 'Siri',
    ),
    'generated' => 
    array (
      'en' => 'Generated',
      'sw' => 'Imetengenezwa',
    ),
    'governance_document' => 
    array (
      'en' => 'Governance Document',
      'sw' => 'Hati ya Utawala',
    ),
    'of' => 
    array (
      'en' => 'of',
      'sw' => 'wa',
    ),
    'page' => 
    array (
      'en' => 'Page',
      'sw' => 'Ukurasa',
    ),
    'report_title' => 
    array (
      'en' => 'MAS Performance Report',
      'sw' => 'Ripoti ya Utendaji ya MAS',
    ),
    'summary' => 
    array (
      'en' => 'Summary',
      'sw' => 'Muhtasari',
    ),
    'system_state' => 
    array (
      'en' => 'System State',
      'sw' => 'Hali ya Mfumo',
    ),
  ),
  'risk' => 
  array (
    'active_risks' => 
    array (
      'en' => 'Active Risks',
      'sw' => 'Hatari Zinazoendelea',
    ),
    'critical_issues' => 
    array (
      'en' => 'Critical Issues',
      'sw' => 'Masuala Muhimu',
    ),
    'data_note' => 
    array (
      'en' => 'All metrics represent risks currently in a non-closed workflow step (Workflow Stage < 8).',
      'sw' => 'Vipimo vyote vinawakilisha hatari ambazo kwa sasa ziko katika hatua ya kazi isiyofungwa (Hatua ya Kazi < 8).',
    ),
    'data_unavailable' => 
    array (
      'en' => 'No active risk data is currently available in the system.',
      'sw' => 'Hakuna data ya hatari inayopatikana katika mfumo kwa sasa.',
    ),
    'distribution_title' => 
    array (
      'en' => 'Risk Level Distribution',
      'sw' => 'Usambazaji wa Ngazi ya Hatari',
    ),
    'health_title' => 
    array (
      'en' => 'Risk Health',
      'sw' => 'Afya ya Hatari',
    ),
    'open_management' => 
    array (
      'en' => 'Open Risk Management',
      'sw' => 'Fungua Usimamizi wa Hatari',
    ),
    'review_schedule' => 
    array (
      'en' => 'Review Schedule',
      'sw' => 'Ratiba ya Mapitio',
    ),
    'reviews_due' => 
    array (
      'en' => 'Reviews due or overdue',
      'sw' => 'Mapitio yanayohitajika au yaliyochelewa',
    ),
    'subtitle' => 
    array (
      'en' => 'Overview of active risks, criticality, and review statuses.',
      'sw' => 'Muhtasari wa hatari zinazoendelea, umuhimu, na hali za mapitio.',
    ),
    'title' => 
    array (
      'en' => 'Risk Metrics',
      'sw' => 'Vipimo vya Hatari',
    ),
  ),
);

        $count = 0;

        foreach ($translations as $group => $items) {
            foreach ($items as $key => $text) {
                TranslationLanguageLine::updateOrCreate(
                    ['group' => 'mas/' . $group, 'key' => $key],
                    ['text' => $text]
                );
                $count++;
            }
        }

        $this->command->info("MAS translations seeded: {$count} keys");
    }
}
