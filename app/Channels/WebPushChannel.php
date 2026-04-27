<?php

namespace App\Channels;

use App\Services\WebPushService;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function __construct(private WebPushService $service) {}

    /**
     * Send the given notification.
     * The notification class must implement toWebPush(object $notifiable): array
     * with keys: title (required), body, action_url, tag (all optional).
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);

        if (empty($payload['title'])) {
            return;
        }

        $this->service->sendToUser($notifiable, $payload);
    }
}
