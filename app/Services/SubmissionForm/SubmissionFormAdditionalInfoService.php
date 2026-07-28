<?php

namespace App\Services\SubmissionForm;

use App\Http\Controllers\MailController;
use App\Models\CRM\CustomerNotification;
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

    public function portalDeepLink(SubmissionFormInstance $instance): ?string
    {
        $base = rtrim((string) config('services.customer_portal.url', ''), '/');
        if ($base === '') {
            return null;
        }

        $formId = (string) ($instance->submission_form_id ?? '');
        $instanceId = (string) ($instance->id ?? '');
        if ($formId === '' || $instanceId === '') {
            return null;
        }

        return $base.'/forms/'.$formId.'/fill?instance_id='.urlencode($instanceId);
    }

    public function notifyCustomer(SubmissionFormInstance $instance, string $message, User $actor): bool
    {
        $email = $this->resolveCustomerEmail($instance);
        $appName = config('app.name', 'LIMS');
        $displayName = $this->resolveCustomerDisplayName($instance);
        $formLabel = (string) ($instance->getDocumentControlNumber() ?? $instance->form_number ?? 'your submission');
        $safeMessage = nl2br(e(trim($message)));
        $actorName = e((string) $actor->name);
        $portalUrl = $this->portalDeepLink($instance);

        $this->createPortalNotification($instance, $formLabel, $message, $portalUrl);

        if ($email === null) {
            return false;
        }

        $body = 'Hi '.e($displayName).',<br><br>'
            .'We need additional information regarding your submission request '
            .'<strong>'.e($formLabel).'</strong>.<br><br>'
            .'<strong>Message from the laboratory:</strong><br>'
            .$safeMessage.'<br><br>';

        if ($portalUrl !== null) {
            $body .= 'Please <a href="'.e($portalUrl).'">log in to the customer portal</a> to provide the requested information.<br><br>';
        } else {
            $body .= 'Please log in to the customer portal to provide the requested information.<br><br>';
        }

        $body .= 'Regards,<br>'.$actorName.'<br>'.$appName;

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

    private function createPortalNotification(
        SubmissionFormInstance $instance,
        string $formLabel,
        string $message,
        ?string $portalUrl
    ): void {
        if (! $instance->crm_customer_id) {
            return;
        }

        $excerpt = \Illuminate\Support\Str::limit(trim($message), 200);
        $description = "Additional information is required for your request {$formLabel}: {$excerpt}";
        if ($portalUrl !== null) {
            $description .= ' Open the portal to respond.';
        }

        try {
            CustomerNotification::query()->create([
                'customer_id' => $instance->crm_customer_id,
                'entity_type' => SubmissionFormInstance::class,
                'entity_id' => $instance->id,
                'notification_type' => CustomerNotification::TYPE_ADDITIONAL_INFO_REQUIRED,
                'notification_description' => $description,
            ]);
        } catch (Throwable $exception) {
            report($exception);
            Log::warning('Failed to create additional info portal notification.', [
                'instance_id' => $instance->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
