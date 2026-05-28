<?php

namespace App\Livewire\SkillsMatrix\Concerns;

trait HasMatrixFlash
{
    public string $flashMessage = '';

    public string $flashType = 'success';

    protected function flash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }
}
