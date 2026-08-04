<?php

namespace App\Console\Commands;

use App\Company;
use App\Console\Support\AmSpecCompanyCleanupTargets;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteLegacyAmSpecCompaniesCommand extends Command
{
    protected $signature = 'companies:delete-legacy-amspec
                            {--force : Actually delete the legacy AmSpec companies}
                            {--dry-run : List matching companies without deleting}
                            {--reassign-to= : Company ID to move users onto before delete (defaults to the remaining active company)}';

    protected $description = 'Delete legacy AmSpec companies (AmSpec + AmSpec Rio Crude Oil Center), reassigning users first to avoid CASCADE user wipes';

    public function handle(): int
    {
        config(['database.default' => 'pgsql']);

        $targetIds = AmSpecCompanyCleanupTargets::companyIds();
        $companies = $this->resolveTargetCompanies($targetIds);

        if ($companies->isEmpty()) {
            $this->warn('No matching legacy AmSpec companies found.');

            return self::SUCCESS;
        }

        $this->info('Legacy companies targeted for deletion:');
        $this->renderCompanyTable($companies);

        $reassignTo = $this->resolveReassignCompanyId($targetIds);
        if ($reassignTo === null) {
            $this->error('Could not resolve a surviving company to reassign users onto. Pass --reassign-to=<uuid>.');

            return self::FAILURE;
        }

        $keepCompany = Company::query()->find($reassignTo);
        if ($keepCompany === null) {
            $this->error("Reassign company not found: {$reassignTo}");

            return self::FAILURE;
        }

        $userCount = User::query()->whereIn('company_id', $companies->pluck('id')->all())->count();
        $this->info("Users currently on targeted companies: {$userCount}");
        $this->info("Will reassign those users to: {$keepCompany->name} ({$keepCompany->id})");

        if ($this->option('dry-run')) {
            foreach ($companies as $company) {
                $this->line("[dry-run] Would delete {$company->name} ({$company->id})");
            }
            $this->comment("Dry-run complete. Would delete {$companies->count()} company(ies) after reassigning {$userCount} user(s).");

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to delete without --force (or preview with --dry-run).');

            return self::FAILURE;
        }

        $deleted = 0;
        $failed = 0;

        try {
            DB::connection('pgsql')->transaction(function () use ($companies, $reassignTo, $userCount, &$deleted): void {
                if ($userCount > 0) {
                    $moved = User::query()
                        ->whereIn('company_id', $companies->pluck('id')->all())
                        ->update(['company_id' => $reassignTo]);
                    $this->info("Reassigned {$moved} user(s).");
                }

                foreach ($companies as $company) {
                    $company->delete();
                    $this->info("Deleted {$company->name} ({$company->id})");
                    $deleted++;
                }
            });
        } catch (Throwable $exception) {
            $failed = $companies->count() - $deleted;
            $this->error('Company delete failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Deleted {$deleted} company(ies); failed {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
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
