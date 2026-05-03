<?php

namespace App\Policies;

use App\Models\AI\AiManualDocument;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AiManualDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, AiManualDocument $document): bool
    {
        return $this->ownsDocument($user, $document) || $this->canManageKnowledge($user);
    }

    public function update(User $user, AiManualDocument $document): bool
    {
        return $this->view($user, $document);
    }

    public function delete(User $user, AiManualDocument $document): bool
    {
        return $this->view($user, $document);
    }

    public function reindex(User $user, AiManualDocument $document): bool
    {
        return $this->view($user, $document);
    }

    private function ownsDocument(User $user, AiManualDocument $document): bool
    {
        return (int) $document->created_by === (int) $user->id;
    }

    private function canManageKnowledge(User $user): bool
    {
        return $user->can('ai.knowledge.manage') || in_array('*', $user->getFlatPermissions(), true);
    }
}