<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid-keys';
    protected $description = 'Generate VAPID public and private keys for Web Push notifications';

    public function handle(): void
    {
        $keys = VAPID::createVapidKeys();

        $this->info('Add these to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->comment('Keep VAPID_PRIVATE_KEY secret. VAPID_PUBLIC_KEY is safe to expose in the browser.');
    }
}
