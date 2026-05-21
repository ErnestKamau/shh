<?php

namespace App\Exceptions\Api\Portal;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalApiException extends Exception
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $httpStatus = 404,
    ) {
        parent::__construct($message, $httpStatus);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
            ],
        ], $this->httpStatus);
    }

    public static function formNotFound(): self
    {
        return new self(
            'form_not_found',
            'We could not find a submission form with that ID.',
            404,
        );
    }

    public static function formNotPortal(): self
    {
        return new self(
            'form_not_portal',
            'This form is not enabled for the customer portal. Turn on "Customer portal form" in Polucon.',
            404,
        );
    }

    public static function formNotActive(): self
    {
        return new self(
            'form_not_active',
            'This form is inactive and cannot be used right now.',
            404,
        );
    }

    public static function formNotPublished(): self
    {
        return new self(
            'form_not_published',
            'This form is not published yet. Publish it in Polucon before customers can access it.',
            404,
        );
    }

    public static function formCustomerNotAllowed(): self
    {
        return new self(
            'form_customer_not_allowed',
            'This form is not available for the selected customer.',
            403,
        );
    }

    public static function instanceNotFound(): self
    {
        return new self(
            'instance_not_found',
            'We could not find a submission with that ID.',
            404,
        );
    }

    public static function instanceCustomerMismatch(): self
    {
        return new self(
            'instance_customer_mismatch',
            'This submission does not belong to the customer specified in X-CRM-Customer-Id.',
            403,
        );
    }

    public static function instancePortalAccountMismatch(): self
    {
        return new self(
            'instance_portal_account_mismatch',
            'This submission does not belong to the portal account specified in X-Portal-Account-Id.',
            403,
        );
    }

    public static function portalContextHeadersRequired(): self
    {
        return new self(
            'portal_context_required',
            'Send X-CRM-Customer-Id or X-Portal-Account-Id to access this submission.',
            403,
        );
    }

    public static function customerRequestFormNotFound(): self
    {
        return new self(
            'customer_request_form_not_found',
            'No active published customer request form is configured. Mark a portal form as the customer request form in Polucon.',
            404,
        );
    }

    public static function instanceNotDeletable(string $status): self
    {
        return new self(
            'instance_not_deletable',
            sprintf(
                'Submissions with status "%s" cannot be deleted from the portal. Only draft or submitted submissions can be removed.',
                $status,
            ),
            422,
        );
    }

    public static function withMessage(string $errorCode, string $message, int $httpStatus = 422): self
    {
        return new self($errorCode, $message, $httpStatus);
    }
}
