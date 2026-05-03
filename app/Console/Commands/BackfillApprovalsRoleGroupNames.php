<?php

namespace App\Console\Commands;

use App\Approvals;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Console\Command;

class BackfillApprovalsRoleGroupNames extends Command
{
    /**
     * The name and signature of the artisan command.
     *
     * @var string
     */
    protected $signature = 'inventory:backfill-approval-role-groups
                            {--database= : Database connection to use (e.g. pgsql)}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Backfill role_group_name in approvals table from legacy role_id references. Maps to Spatie groups based on approval workflow stage and role context.';

    /**
     * Execute the command.
     *
     * @return int
     */
    public function handle()
    {
        $database = $this->option('database') ?: config('database.default');
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting approval role_group_name backfill on [' . $database . ']...' . ($dryRun ? ' (DRY RUN)' : ''));

        // Mapping of legacy role IDs to Spatie role names
        // Based on the legacy roles and Spatie roles in the system
        $roleIdToSpatieRoleMapping = [
            1 => 'Quality Manager',           // Mapped directly
            2 => 'Deputy Lab Manager',        // Mapped directly
            3 => 'Laboratory Analyst',        // Mapped directly
            4 => 'Sample Desk',              // Mapped directly
            10002 => 'Process Chemist',      // Mapped directly
            10004 => 'Lab Manager',          // Mapped directly (could also be Inventory Store Manager Group depending on context)
            10005 => 'Financial Accountant', // Mapped directly (could also be Inventory Finance Group)
            10006 => 'View All System Events',
            10007 => 'Engineering Head Role',
            10008 => 'Can Verify Samples',
            10009 => 'Can Approve Samples',
            10010 => 'Can View Qc Samples',
            10011 => 'Deactivate Personnel',
            10012 => 'Requester',
            10013 => 'Sample Reception',
            10014 => 'Admin',                // Mapped directly
            10015 => 'Procurement',          // Could map to Inventory Procurement Group or stay as-is
            10016 => 'Store',               // Could map to Inventory Store Manager Group or stay as-is
            10017 => 'Admin Skill Matrix',
            10018 => 'Access Personnel',
        ];

        // Mapping specific to Inventory approval workflow stages
        // When a stage is detected, use the appropriate Inventory group
        $stageToInventoryGroupMapping = [
            'Material Requisition' => 'Inventory Department Head Group',
            'Purchase Orders' => 'Inventory Procurement Group',
            'Request for Quotation' => 'Inventory Procurement Group',
            'Request to Store' => 'Inventory Store Manager Group',
            'Goods Receipt' => 'Inventory Store Manager Group',
            'Goods Return' => 'Inventory Store Manager Group',
            'Gate Pass' => 'Inventory Store Manager Group',
            'Purchase Order' => 'Inventory Procurement Group',
            'Quotation' => 'Inventory Procurement Group',
            'Invoice' => 'Inventory Finance Group',
        ];

        $approvals = Approvals::on($database)->whereNull('role_group_name')->get();
        $totalCount = $approvals->count();
        $updatedCount = 0;

        if ($totalCount === 0) {
            $this->info('No approvals found with null role_group_name. Migration complete or already done.');
            return 0;
        }

        $this->info("Found {$totalCount} approvals needing backfill.");
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        foreach ($approvals as $approval) {
            $roleGroupName = null;

            // First, try to map by stage to Inventory-specific group
            if (!empty($approval->stage)) {
                $roleGroupName = $stageToInventoryGroupMapping[$approval->stage] ?? null;
            }

            // If no stage-based mapping, try to map by role_id to Spatie role name
            if (!$roleGroupName && isset($roleIdToSpatieRoleMapping[$approval->role_id])) {
                $roleGroupName = $roleIdToSpatieRoleMapping[$approval->role_id];
            }

            // If still no mapping, log a warning
            if (!$roleGroupName) {
                $this->warn("  Could not map approval id={$approval->id}, role_id={$approval->role_id}, stage={$approval->stage}");
                $bar->advance();
                continue;
            }

            if (!$dryRun) {
                $approval->setConnection($database)->update(['role_group_name' => $roleGroupName]);
            }

            $updatedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("DRY RUN: Would have updated {$updatedCount}/{$totalCount} approvals.");
            $this->info("Run without --dry-run flag to apply changes.");
        } else {
            $this->info("Successfully updated {$updatedCount}/{$totalCount} approvals with role_group_name.");
        }

        // Verify Spatie roles exist
        $this->info("\nVerifying Spatie role groups exist...");
        $rolesUsed = Approvals::on($database)
            ->whereNotNull('role_group_name')
            ->distinct('role_group_name')
            ->pluck('role_group_name')
            ->toArray();

        foreach ($rolesUsed as $roleName) {
            $exists = SpatieRole::on($database)
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->exists();
            $status = $exists ? '[OK]' : '[MISSING]';
            $this->line("  {$status} {$roleName}");
        }

        return 0;
    }
}
