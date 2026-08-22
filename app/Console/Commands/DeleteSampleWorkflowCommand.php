<?php

namespace App\Console\Commands;

use App\Services\Ops\SampleWorkflowCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteSampleWorkflowCommand extends Command
{
    private const PRODUCTION_CONFIRMATION = 'DELETE-ALL-SAMPLE-WORKFLOW';

    protected $signature = 'ops:delete-sample-workflow
                            {--force : Confirm deletion of all sample batches, requests, and schedules}
                            {--production-confirm= : Required production safety phrase}';

    protected $description = 'Delete sample jobs (headers), requests, captured results, TRF instances, and sampling schedules';

    public function handle(SampleWorkflowCleanupService $service): int
    {
        if (! $this->option('force')) {
            $this->error('This command permanently deletes all sample workflow operational data.');
            $this->line('Run with --force to proceed.');

            return self::FAILURE;
        }

        if ($this->laravel->environment('production')
            && $this->option('production-confirm') !== self::PRODUCTION_CONFIRMATION) {
            $this->error('Production execution requires the explicit confirmation phrase.');
            $this->line('--production-confirm='.self::PRODUCTION_CONFIRMATION);

            return self::FAILURE;
        }

        $this->warn('Deleting sample jobs, requests, captured results, request TRF instances, and sampling schedules…');
        $this->line('TRF templates (Food/Water), catalog, users, quotations, and pricelists are not touched.');

        try {
            $counts = DB::transaction(static fn (): array => $service->deleteAll());
        } catch (\Throwable $exception) {
            $this->error('Sample workflow cleanup failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Sample workflow data cleared.');
        $this->table(
            ['Target', 'Affected rows'],
            collect($counts)->map(fn (int $count, string $target): array => [$target, (string) $count])->values()->all(),
        );

        return self::SUCCESS;
    }
}
