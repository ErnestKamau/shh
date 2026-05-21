<?php

namespace App\Services\SubmissionForm;

use App\Models\CRM\CustomerNotification;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceNote;
use App\User;
use Illuminate\Support\Str;

class SubmissionFormInstanceNoteService
{
    public function addNote(
        SubmissionFormInstance $instance,
        User $user,
        string $body,
        string $visibility = SubmissionFormInstanceNote::VISIBILITY_INTERNAL
    ): SubmissionFormInstanceNote {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('Note body cannot be empty.');
        }

        if (! in_array($visibility, [
            SubmissionFormInstanceNote::VISIBILITY_INTERNAL,
            SubmissionFormInstanceNote::VISIBILITY_PUBLIC,
        ], true)) {
            throw new \InvalidArgumentException('Invalid note visibility.');
        }

        $note = SubmissionFormInstanceNote::query()->create([
            'submission_form_instance_id' => $instance->id,
            'body' => $body,
            'visibility' => $visibility,
            'created_by' => $user->id,
        ]);

        if ($visibility === SubmissionFormInstanceNote::VISIBILITY_PUBLIC) {
            $this->notifyCustomer($instance, $note);
        }

        return $note->load('author');
    }

    private function notifyCustomer(SubmissionFormInstance $instance, SubmissionFormInstanceNote $note): void
    {
        if (! $instance->crm_customer_id) {
            return;
        }

        $formNumber = $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Request';
        $excerpt = Str::limit($note->body, 200);

        CustomerNotification::query()->create([
            'customer_id' => $instance->crm_customer_id,
            'entity_type' => SubmissionFormInstance::class,
            'entity_id' => $instance->id,
            'notification_type' => CustomerNotification::TYPE_REQUEST_NOTE,
            'notification_description' => "A new message was posted on your request {$formNumber}: {$excerpt}",
        ]);
    }
}
