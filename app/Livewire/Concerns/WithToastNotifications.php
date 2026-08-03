<?php

namespace App\Livewire\Concerns;

trait WithToastNotifications
{
    protected function toast(string $type, string $message): void
    {
        $this->dispatch('notify', type: $type, message: $message);
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
