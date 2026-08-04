<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DeletesUsersSafely;
use App\Console\Support\AmSpecUserCleanupEmails;
use Illuminate\Console\Command;

class DeleteUsersExceptRetainedCommand extends Command
{
    use DeletesUsersSafely;

    protected $signature = 'users:delete-except-retained
                            {--force : Actually delete non-retained users}
                            {--dry-run : List matching users without deleting}';

    protected $description = 'Delete every user except the LIMS sheet roster plus retained operators (Nancy, Karokin Portal, Coleman, Ernest, Fridah)';

    public function handle(): int
    {
        $this->usePgsqlConnection();

        $keepEmails = AmSpecUserCleanupEmails::normalized(AmSpecUserCleanupEmails::retainedEmails());
        $keepNames = AmSpecUserCleanupEmails::retainedOperatorNames();
        $users = $this->usersExcludingEmailsAndNames($keepEmails, $keepNames);

        $this->info('Retained email allow-list ('.count($keepEmails).'):');
        foreach ($keepEmails as $email) {
            $this->line("  - {$email}");
        }
        $this->info('Retained name allow-list ('.count($keepNames).'):');
        foreach ($keepNames as $name) {
            $this->line("  - {$name}");
        }
        $this->newLine();

        if ($users->isEmpty()) {
            $this->warn('No non-retained users found to delete.');

            return self::SUCCESS;
        }

        $this->info('Users targeted for deletion:');
        $this->renderUserTable($users);

        if ($this->option('dry-run')) {
            $result = $this->deleteUsers($users, true);
            $this->comment("Dry-run complete. Would delete {$result['deleted']} user(s).");

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Refusing to delete without --force (or preview with --dry-run).');

            return self::FAILURE;
        }

        $result = $this->deleteUsers($users, false);
        $this->newLine();
        $this->info("Deleted {$result['deleted']} user(s); failed {$result['failed']}.");

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
