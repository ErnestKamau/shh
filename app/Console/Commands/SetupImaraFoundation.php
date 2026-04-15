<?php

namespace App\Console\Commands;

use App\Services\AI\Repository\AiRepositorySetupService;
use Illuminate\Console\Command;

class SetupImaraFoundation extends Command
{
    protected $signature = 'imara:setup-foundation
                            {--skip-pgvector : Skip enabling the pgvector extension during setup}';

    protected $description = 'Provision the PostgreSQL reporting and AI repository foundation for Imara LIMS';

    public function handle(AiRepositorySetupService $setupService): int
    {
        try {
            $result = $setupService->setup(! $this->option('skip-pgvector'));
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Repository connection: {$result['connection']}");
        $this->info('Schemas ensured: ' . implode(', ', $result['schemas']));
        $this->info('pgvector enabled: ' . ($result['pgvector_enabled'] ? 'yes' : 'no'));
        $this->info('Operational reporting foundation is ready.');

        return self::SUCCESS;
    }
}
