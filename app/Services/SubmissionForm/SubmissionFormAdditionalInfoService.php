<?php

namespace App\Services\SubmissionForm;

use App\Http\Controllers\MailController;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionFormAdditionalInfoService
{
    /**
     * @var array<int, string>
     */
    private const EMAIL_FIELD_HINTS = [
        'customer_email',
        'email',
        'contact_email',
    ];

    public function resolveCustomerEmail(SubmissionFormInstance $instance): ?string
    {
        $instance->loadMissing(['crmCustomer', 'submittedBy', 'values.element']);

        $fromCustomer = trim((string) ($instance->crmCustomer?->email ?? ''));
        if ($fromCustomer !== '' && filter_var($fromCustomer, FILTER_VALIDATE_EMAIL)) {
            return $fromCustomer;
        }

        $fromSubmitter = trim((string) ($instance->submittedBy?->email ?? ''));
        if ($fromSubmitter !== '' && filter_var($fromSubmitter, FILTER_VALIDATE_EMAIL)) {
            return $fromSubmitter;
        }

        foreach ($instance->values as $row) {
            $element = $row->element;
            if ($element === null) {
                continue;
            }

            $mappingField = strtolower(trim((string) ($element->mapping_field ?? '')));
            $elementName = strtolower(trim((string) ($element->name ?? '')));
            $value = trim((string) ($row->value ?? ''));

            if ($value === '' || ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            foreach (self::EMAIL_FIELD_HINTS as $hint) {
                if (($mappingField !== '' && str_contains($mappingField, $hint))
                    || ($elementName !== '' && str_contains($elementName, $hint))) {
                    return $value;
                }
            }
        }

        return null;
    }

    public function resolveCustomerDisplayName(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing(['crmCustomer', 'submittedBy']);

        $name = trim((string) ($instance->crmCustomer?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return trim((string) ($instance->submittedBy?->name ?? '')) ?: 'Customer';
    }

    public function notifyCustomer(SubmissionFormInstance $instance, string $message, User $actor): bool
    {
        $email = $this->resolveCustomerEmail($instance);
        if ($email === null) {
            return false;
        }

        $appName = config('app.name', 'LIMS');
        $displayName = $this->resolveCustomerDisplayName($instance);
        $formLabel = (string) ($instance->getDocumentControlNumber() ?? $instance->form_number ?? 'your submission');
        $safeMessage = nl2br(e(trim($message)));
        $actorName = e((string) $actor->name);

        $body = 'Hi '.e($displayName).',<br><br>'
            .'We need additional information regarding your submission request '
            .'<strong>'.e($formLabel).'</strong>.<br><br>'
            .'<strong>Message from the laboratory:</strong><br>'
            .$safeMessage.'<br><br>'
            .'Please log in to the customer portal to provide the requested information.<br><br>'
            .'Regards,<br>'.$actorName.'<br>'.$appName;

        try {
            (new MailController)->html_email([
                'contacts' => [$email],
                'subject' => '['.$appName.'] Additional information required — '.$formLabel,
                'body' => $body,
            ], 'default');

            return true;
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Failed to send additional info request email.', [
                'instance_id' => $instance->id,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
