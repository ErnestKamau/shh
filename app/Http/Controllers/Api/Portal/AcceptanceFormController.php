<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\SignAcceptanceFormRequest;
use App\Http\Resources\Portal\AcceptanceFormResource;
use App\Models\CRM\CustomerNotification;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcceptanceFormController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $customerId = (string) $request->header('X-CRM-Customer-Id', '');
        if ($customerId === '') {
            return response()->json(['message' => 'X-CRM-Customer-Id header is required.'], 422);
        }

        $forms = AnalysisAcceptanceForm::query()
            ->with(['lines.sampleType', 'lines.analysisType'])
            ->where('crm_customer_id', $customerId)
            ->whereIn('status', [
                AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN,
                AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
                AnalysisAcceptanceForm::STATUS_COMPLETED,
            ])
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        return AcceptanceFormResource::collection($forms);
    }

    public function show(Request $request, AnalysisAcceptanceForm $acceptanceForm): AcceptanceFormResource|JsonResponse
    {
        if (!$this->belongsToCustomer($request, $acceptanceForm)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $acceptanceForm->load(['lines.sampleType', 'lines.analysisType', 'lines.analysisElement']);

        return new AcceptanceFormResource($acceptanceForm);
    }

    public function sign(
        SignAcceptanceFormRequest $request,
        AnalysisAcceptanceForm $acceptanceForm,
        AcceptanceFormService $acceptanceFormService
    ): AcceptanceFormResource|JsonResponse {
        if (!$this->belongsToCustomer($request, $acceptanceForm)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validated();

        $form = $acceptanceFormService->recordCustomerSignature(
            $acceptanceForm,
            (string) $validated['customer_signer_name'],
            (string) $validated['customer_signature'],
            $validated['customer_signed_at'] ?? null
        );

        CustomerNotification::query()
            ->where('customer_id', $acceptanceForm->crm_customer_id)
            ->where('entity_type', AnalysisAcceptanceForm::class)
            ->where('entity_id', $acceptanceForm->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $form->load(['lines.sampleType', 'lines.analysisType']);

        return new AcceptanceFormResource($form);
    }

    public function markNotificationRead(Request $request, string $notificationId): JsonResponse
    {
        $customerId = (string) $request->header('X-CRM-Customer-Id', '');
        $notification = CustomerNotification::query()->find($notificationId);

        if (!$notification || (string) $notification->customer_id !== $customerId) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function belongsToCustomer(Request $request, AnalysisAcceptanceForm $form): bool
    {
        $customerId = (string) $request->header('X-CRM-Customer-Id', '');

        return $customerId !== '' && (string) $form->crm_customer_id === $customerId;
    }
}
