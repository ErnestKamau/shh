<?php

namespace App\Support;

use Livewire\Component;

final class TrfToast
{
    public static function dispatch(Component $component, string $type, string $message, ?string $title = null, ?string $variant = null): void
    {
        $component->dispatch('imara-toast', [
            'type' => $type,
            'message' => $message,
            'title' => $title ?? self::titleFor($type),
            'variant' => $variant ?? self::variantFor($type),
        ]);
    }

    /**
     * @param  array{type?: string, message?: string, title?: string, variant?: string}  $payload
     */
    public static function flash(array $payload): void
    {
        $type = (string) ($payload['type'] ?? 'info');

        session()->flash('imara_toast', [
            'type' => $type,
            'message' => (string) ($payload['message'] ?? ''),
            'title' => (string) ($payload['title'] ?? self::titleFor($type)),
            'variant' => (string) ($payload['variant'] ?? self::variantFor($type)),
        ]);
    }

    public static function flashMessage(string $type, string $message, ?string $title = null, ?string $variant = null): void
    {
        self::flash([
            'type' => $type,
            'message' => $message,
            'title' => $title,
            'variant' => $variant,
        ]);
    }

    private static function titleFor(string $type): string
    {
        return match (strtolower($type)) {
            'success' => 'Saved',
            'error' => 'Could not complete',
            'warning' => 'Please review',
            default => 'Update',
        };
    }

    private static function variantFor(string $type): string
    {
        return match (strtolower($type)) {
            'success' => 'celebrate',
            'error' => 'shake',
            'warning' => 'pulse',
            default => 'glass',
        };
    }
}
