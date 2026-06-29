<?php

namespace Database\Seeders;

use Database\Seeders\Setup\PersonnelPermissionsSeeder;
use Database\Seeders\Setup\AdminGroupPermissionsSeeder;
use Database\Seeders\Setup\CRMPermissionsSeeder;
use Database\Seeders\Setup\EquipmentPermissionsSeeder;
use Database\Seeders\Setup\Languages\MasLanguageDatabaseSeeder;
use Database\Seeders\Setup\Languages\CRMLanguageSeeder;
use Database\Seeders\Setup\Languages\EquipmentLanguageSeeder;
use Database\Seeders\Setup\Languages\LabDashboardLanguageSeeder;
use Database\Seeders\Setup\Languages\PersonnelLanguageSeeder;
use Database\Seeders\Setup\Languages\SystemTranslationsSeeder;
use Database\Seeders\Setup\AuditModulePermissionsSeeder;
use Database\Seeders\Setup\RiskModulePermissionsSeeder;
use Database\Seeders\Setup\DmsModulePermissionsSeeder;
use Database\Seeders\Setup\LabModulePermissionsSeeder;
use Database\Seeders\Setup\SystemConfigPermissionsSeeder;
use Database\Seeders\Setup\RegistryModuleDataSeeder;
use Database\Seeders\Setup\SystemSetupSeeder;
use Database\Seeders\Setup\QuotationReportConfigSeeder;
use Database\Seeders\Setup\WorkflowResponsibilityConfigSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemSetupSeeder::class,
            \Database\Seeders\Setup\SkillsMatrixPermissionsSeeder::class,
            RegistryModuleDataSeeder::class,
            WorkflowResponsibilityConfigSeeder::class,
            AmSpecQuotationTermsSeeder::class,
            QuotationReportConfigSeeder::class,
            PersonnelPermissionsSeeder::class,
            CRMPermissionsSeeder::class,
            LabModulePermissionsSeeder::class,
            AuditModulePermissionsSeeder::class,
            RiskModulePermissionsSeeder::class,
            DmsModulePermissionsSeeder::class,
            // Optional: php artisan db:seed --class=Database\\Seeders\\DmsModuleWorkflowSeeder
            EquipmentPermissionsSeeder::class,
            ReportingUnitsSeeder::class,
            SystemConfigPermissionsSeeder::class,
            SystemTranslationsSeeder::class,
            PersonnelLanguageSeeder::class,
            CRMLanguageSeeder::class,
            EquipmentLanguageSeeder::class,
            LabDashboardLanguageSeeder::class,
            // Keep MAS translations last so migrated MAS keys win on overlap.
            MasLanguageDatabaseSeeder::class,
            AdminGroupPermissionsSeeder::class,
            LaboratoryServiceRequestFormSeeder::class,
            // GCLA01FormSeeder::class,
            // PF180FormSeeder::class,
            // DCEA001FormSeeder::class,
            ClearsSampleWorkflowDataSeeder::class,
            Phase1FoundationSeeder::class,
            Phase2LocationSeeder::class,
            Phase3CrmMasterDataSeeder::class,
            Phase4SampleTaxonomySeeder::class,
            Phase5LaboratoryOrganizationSeeder::class,
            Phase6PersonnelLabInsightsSeeder::class,
            Phase7InventoryManagementSeeder::class,
            Phase8AnalyticalParameterMatrixSeeder::class,
            // Portal TRF forms — must run after Phase 8 so sample-type links match current taxonomy IDs.
            SubmissionFormTrfWaterSeeder::class,
            SubmissionFormTrfFoodSeeder::class,
            SubmissionFormTrfWasteWaterSeeder::class,
            LabAnalysisAcceptanceFormSeeder::class,
            Phase13FoodStandardsAndPricelistSeeder::class,
            Phase9SampleWorkflowSeeder::class,
            Phase10AnalyticalResultsSeeder::class,
            Phase11QcAnalyticsSeeder::class,
            Phase12EquipmentManagementSeeder::class,
            SkillsMatrixSeeder::class,
        ]);

        // Auto-assign admin role to imported dump users if they exist in the database
        $dumpEmails = ['dannyagah13+customer1@gmail.com', 'dannyagah13+staff2@gmail.com', 'jacobmwalughs@gmail.com'];
        \App\User::whereIn('email', $dumpEmails)->get()->each(function ($user) {
            $user->assignRole('admin');
        });
    }
}
