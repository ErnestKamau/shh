<?php

namespace App\Services\Portal;

use App\Http\Controllers\MailController;
use App\Models\PortalAccessRequest;
use Illuminate\Support\Facades\Http;

class PortalAccessInvitationService
{
    /**
     * @return array{invitation_url:string,expires_at:string,token:string}
     */
    public function createInvite(PortalAccessRequest $accessRequest, ?int $reviewedByUserId = null): array
    {
        $baseUrl = rtrim((string) config('services.portal_relay.auth_api_base_url'), '/');
        $sharedKey = (string) config('services.portal_relay.shared_key');

        if ($baseUrl === '') {
            abort(500, 'Portal relay auth API base URL is not configured.');
        }

        if ($sharedKey === '') {
            abort(500, 'Portal relay shared key is not configured.');
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->withHeaders(['X-Relay-Key' => $sharedKey])
            ->post($baseUrl.'/api/v1/auth/access-invites', [
                'full_name_or_organisation' => (string) ($accessRequest->full_name_or_organisation ?? ''),
                'address' => (string) ($accessRequest->address ?? ''),
                'zone' => (string) ($accessRequest->zone ?? ''),
                'tin_number' => (string) ($accessRequest->tin_number ?? ''),
                'email' => (string) ($accessRequest->email ?? ''),
                'phone_number' => (string) ($accessRequest->phone_number ?? ''),
                'postal_code' => (string) ($accessRequest->postal_code ?? ''),
                'source_request_id' => (string) $accessRequest->id,
                'reviewed_by_user_id' => $reviewedByUserId,
            ]);

        if ($response->failed()) {
            $message = (string) ($response->json('message') ?? 'Failed to create portal invite.');
            abort($response->status() >= 400 ? $response->status() : 500, $message);
        }

        $data = (array) $response->json('data', []);

        return [
            'invitation_url' => (string) ($data['invitation_url'] ?? ''),
            'expires_at' => (string) ($data['expires_at'] ?? ''),
            'token' => (string) ($data['token'] ?? ''),
        ];
    }

    public function sendApprovalEmail(string $email, string $displayName, string $invitationUrl): void
    {
        $appName = config('app.name', 'LIMS');
        $safeName = trim($displayName) !== '' ? $displayName : 'Client';

        $body = 'Hi '.$safeName.',<br><br>'
            .'Your portal access request has been approved.<br><br>'
            .'Use the link below to create your account and set your password:<br><br>'
            .'<a href="'.$invitationUrl.'">Create Account</a><br><br>'
            .'If you did not request this, please contact support.';

        (new MailController)->html_email([
            'contacts' => [$email],
            'subject' => '['.$appName.'] Portal Access Approved',
            'body' => $body,
        ], 'default');
    }

    public function sendRejectionEmail(string $email, string $displayName, ?string $reason = null): void
    {
        $appName = config('app.name', 'LIMS');
        $safeName = trim($displayName) !== '' ? $displayName : 'Client';
        $reasonLine = trim((string) $reason) !== ''
            ? 'Reason: '.e(trim((string) $reason)).'<br><br>'
            : '';

        $body = 'Hi '.$safeName.',<br><br>'
            .'Your portal access request has been reviewed and was not approved at this time.<br><br>'
            .$reasonLine
            .'If you need support, please contact the laboratory team.';

        (new MailController)->html_email([
            'contacts' => [$email],
            'subject' => '['.$appName.'] Portal Access Request Update',
            'body' => $body,
        ], 'default');
    }
}
