<?php

namespace App\Services\Sampleworkflow;

use App\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves whether a new job/batch should be created in the technical (sandbox) lane.
 */
class TechnicalSandboxContext
{
    public function shouldCreateAsTechnical(?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return (bool) $user->is_technical;
    }

    public function resolveCreatingUser(?string $userId = null): ?User
    {
        if (is_string($userId) && $userId !== '') {
            $user = User::query()->find($userId);
            if ($user instanceof User) {
                return $user;
            }
        }

        $authUser = Auth::user();

        return $authUser instanceof User ? $authUser : null;
    }
}
