<?php

namespace App\Console\Commands;

use App\Company;
use App\Console\Support\AmSpecCompanyCleanupTargets;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DeleteLegacyAmSpecCompaniesCommand extends Command
{
    protected $signature = 'companies:delete-legacy-amspec
                            {--force : Actually delete the legacy AmSpec companies}
                            {--dry-run : List matching companies without deleting}
                            {--reassign-to= : Company ID to move owned rows onto before delete (defaults to the remaining active company)}';

    protected $description = 'Delete legacy AmSpec companies (AmSpec + AmSpec Rio Crude Oil Center), reassigning CASCADE-owned rows first to avoid wiping sample types / analysis data / users';

    public function handle(): int
    {
        config(['database.default' => 'pgsql']);

        $targetIds = AmSpecCompanyCleanupTargets::companyIds();
        $companies = $this->resolveTargetCompanies($targetIds);

        if ($companies->isEmpty()) {
            $this->warn('No matching legacy AmSpec companies found.');

            return self::SUCCESS;
        }

        $fromIds = $companies->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->info('Legacy companies targeted for deletion:');
        $this->renderCompanyTable($companies);

        $reassignTo = $this->resolveReassignCompanyId($targetIds);
        if ($reassignTo === null) {
            $this->error('Could not resolve a surviving company to reassign data onto. Pass --reassign-to=<uuid>.');

            return self::FAILURE;
        }

        $keepCompany = Company::query()->find($reassignTo);
        if ($keepCompany === null) {
            $this->error("Reassign company not found: {$reassignTo}");

            return self::FAILURE;
        }

        $this->info("Will reassign CASCADE-owned rows to: {$keepCompany->name} ({$keepCompany->id})");
        $plannedMoves = $this->countCascadeOwnedRows($fromIds);
        $this->renderReassignmentPlan($plannedMoves);

        if ($this->option('dry-run')) {
            foreach ($companies as $company) {
                $this->line("[dry-run] Would delete {$company->name} ({$company->id})");
            }
            $this->comment('Dry-run complete. No rows were modified.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to delete without --force (or preview with --dry-run).');

            return self::FAILURE;
        }

        $deleted = 0;

        try {
            DB::connection('pgsql')->transaction(function () use ($companies, $fromIds, $reassignTo, &$deleted): void {
                $this->reassignCascadeOwnedRows($fromIds, $reassignTo);

                foreach ($companies as $company) {
                    $company->delete();
                    $this->info("Deleted {$company->name} ({$company->id})");
                    $deleted++;
                }
            });
        } catch (Throwable $exception) {
            $this->error('Company delete failed (transaction rolled back): '.$exception->getMessage());
            $this->warn('No companies were deleted. Fix conflicts (often duplicate codes on the destination company) and retry.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Deleted {$deleted} company(ies).");

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $fromIds
     * @return array<string, int>
     */
    protected function countCascadeOwnedRows(array $fromIds): array
    {
        $counts = [];

        foreach ($this->companyCascadeTables() as $table) {
            $count = (int) DB::connection('pgsql')
                ->table($table)
                ->whereIn('company_id', $fromIds)
                ->count();

            if ($count > 0) {
                $counts[$table] = $count;
            }
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @param  array<string, int>  $plannedMoves
     */
    protected function renderReassignmentPlan(array $plannedMoves): void
    {
        if ($plannedMoves === []) {
            $this->line('No CASCADE-owned rows found on targeted companies.');

            return;
        }

        $this->table(
            ['Table', 'Rows to reassign'],
            collect($plannedMoves)->map(fn (int $count, string $table): array => [$table, $count])->values()->all()
        );
    }

    /**
     * @param  list<string>  $fromIds
     */
    protected function reassignCascadeOwnedRows(array $fromIds, string $toCompanyId): void
    {
        foreach ($this->companyCascadeTables() as $table) {
            $moved = DB::connection('pgsql')
                ->table($table)
                ->whereIn('company_id', $fromIds)
                ->update(['company_id' => $toCompanyId]);

            if ($moved > 0) {
                $this->line("  Reassigned {$moved} {$table} row(s)");
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function companyCascadeTables(): array
    {
        $rows = DB::connection('pgsql')->select("
            SELECT DISTINCT tc.table_name
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            JOIN information_schema.referential_constraints AS rc
                ON tc.constraint_name = rc.constraint_name
                AND tc.table_schema = rc.constraint_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = 'public'
                AND ccu.table_name = 'companies'
                AND kcu.column_name = 'company_id'
                AND rc.delete_rule = 'CASCADE'
            ORDER BY tc.table_name
        ");

        $tables = [];

        foreach ($rows as $row) {
            $table = (string) $row->table_name;
            if ($table === 'companies') {
                continue;
            }
            if (! Schema::connection('pgsql')->hasTable($table)) {
                continue;
            }
            if (! Schema::connection('pgsql')->hasColumn($table, 'company_id')) {
                continue;
            }
            $tables[] = $table;
        }

        return $tables;
    }

    /**
     * @param  list<string>  $targetIds
     * @return Collection<int, Company>
     */
    protected function resolveTargetCompanies(array $targetIds): Collection
    {
        $byId = Company::query()->whereIn('id', $targetIds)->get();

        if ($byId->count() === count($targetIds)) {
            return $byId->sortBy('name')->values();
        }

        $emails = array_map('strtolower', AmSpecCompanyCleanupTargets::companyEmails());
        $names = array_map(
            static fn (string $name): string => strtolower(trim($name)),
            AmSpecCompanyCleanupTargets::companyNames(),
        );

        return Company::query()
            ->get()
            ->filter(function (Company $company) use ($targetIds, $emails, $names): bool {
                if (in_array($company->id, $targetIds, true)) {
                    return true;
                }

                $email = strtolower(trim((string) $company->email));
                $name = strtolower(trim((string) $company->name));

                return in_array($email, $emails, true) || in_array($name, $names, true);
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * @param  list<string>  $targetIds
     */
    protected function resolveReassignCompanyId(array $targetIds): ?string
    {
        $explicit = $this->option('reassign-to');
        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        $survivor = Company::query()
            ->whereNotIn('id', $targetIds)
            ->where('active', true)
            ->orderBy('created_at')
            ->first();

        if ($survivor !== null) {
            return (string) $survivor->id;
        }

        $any = Company::query()
            ->whereNotIn('id', $targetIds)
            ->orderByDesc('active')
            ->orderBy('created_at')
            ->first();

        return $any?->id !== null ? (string) $any->id : null;
    }

    /**
     * @param  Collection<int, Company>  $companies
     */
    protected function renderCompanyTable(Collection $companies): void
    {
        $this->table(
            ['ID', 'Name', 'Email', 'Location', 'Active'],
            $companies->map(fn (Company $company): array => [
                $company->id,
                $company->name,
                $company->email,
                $company->location,
                $company->active ? 'yes' : 'no',
            ])->all()
        );
    }
}
