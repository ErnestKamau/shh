<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DeletesUsersSafely;
use App\Console\Support\AmSpecUserCleanupEmails;
use Illuminate\Console\Command;

class DeleteLimsSheetUsersCommand extends Command
{
    use DeletesUsersSafely;

    protected $signature = 'users:delete-lims-sheet
                            {--force : Actually delete the LIMS sheet users}
                            {--dry-run : List matching users without deleting}';

    protected $description = 'Delete the AmSpec LIMS sheet roster users (10 Middle East Agri & Food users)';

    public function handle(): int
    {
        $this->usePgsqlConnection();

        $emails = AmSpecUserCleanupEmails::normalized(AmSpecUserCleanupEmails::limsSheetEmails());
        $users = $this->usersMatchingEmails($emails);

        if ($users->isEmpty()) {
            $this->warn('No LIMS sheet users found.');

            return self::SUCCESS;
        }

        $this->info('LIMS sheet users targeted for deletion:');
        $this->renderUserTable($users);

        $missing = array_values(array_diff(
            $emails,
            $users->map(fn ($user) => strtolower((string) $user->email))->all()
        ));

        if ($missing !== []) {
            $this->warn('Emails not found in database:');
            foreach ($missing as $email) {
                $this->line("  - {$email}");
            }
        }

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
