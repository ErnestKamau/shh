<?php

namespace App\Livewire\Concerns;

trait WithToastNotifications
{
    protected function toast(string $type, string $message, ?string $title = null): void
    {
        $this->dispatch('notify', type: $type, message: $message);

        $this->dispatch('imara-toast', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'durationMs' => 7000,
        ]);
    }

    protected function imaraToast(string $type, string $title, string $message, int $durationMs = 7000): void
    {
        $this->dispatch('imara-toast', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'durationMs' => $durationMs,
        ]);
    }

    protected function toastSuccess(string $message): void
    {
        $this->toast('success', $message);
    }

    protected function toastError(string $message): void
    {
        $this->toast('error', $message);
    }

    protected function toastWarning(string $message): void
    {
        $this->toast('warning', $message);
    }

    protected function toastInfo(string $message): void
    {
        $this->toast('info', $message);
    }
}
