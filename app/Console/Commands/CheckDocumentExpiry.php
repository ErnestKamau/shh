<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DMS\DocumentExpiryService;

class CheckDocumentExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring documents and send notifications';

    protected $expiryService;

    /**
     * Create a new command instance.
     *
     * @param DocumentExpiryService $expiryService
     * @return void
     */
    public function __construct(DocumentExpiryService $expiryService)
    {
        parent::__construct();
        $this->expiryService = $expiryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Checking for expiring documents...');

        $results = $this->expiryService->checkAndNotifyExpiringDocuments();

        $this->info("Documents checked: {$results['documents_checked']}");
        $this->info("Notifications sent: {$results['notifications_sent']}");
        $this->warn("Documents expired: {$results['documents_expired']}");

        $this->info('Document expiry check completed successfully.');

        return 0;
    }
}

