<?php

namespace App\Http\Controllers\Lab\Samples;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sampleworkflow\StoreCollectionQrExtrasRequest;
use App\Models\SamplingSchedule;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\CollectionQrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Printable pre-collection container QR stickers (separate from the Sample Collection Label).
 */
class CollectionQrCodeController extends Controller
{
    public function __construct(private readonly CollectionQrCodeService $collectionQrCodes) {}

    public function instance(Request $request, SubmissionFormInstance $instance): View
    {
        $codes = $this->collectionQrCodes->codesForInstance($instance, $this->userId());

        return $this->printView($request, [
            'title' => 'Collection QR codes',
            'subtitle' => 'TRF '.($instance->form_number ?: $instance->title ?: $instance->id),
            'stickers' => $this->collectionQrCodes->stickersForInstance($instance, $codes),
            'rowCount' => $this->collectionQrCodes->trfRowCount($instance),
            'printUrl' => route('submission-forms.instances.collection-qr-codes', ['instance' => $instance->id]),
            'extrasUrl' => route('submission-forms.instances.collection-qr-codes.extra', ['instance' => $instance->id]),
            'backUrl' => $instance->submission_form_id
                ? route('submission-forms.instances.show', ['submissionForm' => $instance->submission_form_id, 'instance' => $instance->id])
                : null,
        ]);
    }

    public function storeInstanceExtras(StoreCollectionQrExtrasRequest $request, SubmissionFormInstance $instance): RedirectResponse
    {
        $quantity = (int) $request->validated('quantity');
        $this->collectionQrCodes->addInstanceExtras($instance, $quantity, $this->userId());

        return redirect()
            ->route('submission-forms.instances.collection-qr-codes', array_merge(
                ['instance' => $instance->id],
                $this->printOptions($request),
            ))
            ->with('status', $this->extrasAddedMessage($quantity));
    }

    public function schedule(Request $request, string $schedule): View
    {
        $samplingSchedule = $this->findSchedule($schedule);
        $codes = $this->collectionQrCodes->codesForSchedule($samplingSchedule, $this->userId());

        return $this->printView($request, [
            'title' => 'Collection QR codes',
            'subtitle' => trim((string) ($samplingSchedule->title ?: 'Sampling schedule')),
            'stickers' => $this->collectionQrCodes->stickersForSchedule($samplingSchedule, $codes),
            'rowCount' => max(1, (int) ($samplingSchedule->number_of_samples ?? 0)),
            'printUrl' => route('system-planner.schedule-sampling.collection-qr-codes', ['schedule' => $samplingSchedule->id]),
            'extrasUrl' => route('system-planner.schedule-sampling.collection-qr-codes.extra', ['schedule' => $samplingSchedule->id]),
            'backUrl' => route('system-planner.schedule-sampling.show', ['schedule' => $samplingSchedule->id]),
        ]);
    }

    public function storeScheduleExtras(StoreCollectionQrExtrasRequest $request, string $schedule): RedirectResponse
    {
        $samplingSchedule = $this->findSchedule($schedule);
        $quantity = (int) $request->validated('quantity');
        $this->collectionQrCodes->addScheduleExtras($samplingSchedule, $quantity, $this->userId());

        return redirect()
            ->route('system-planner.schedule-sampling.collection-qr-codes', array_merge(
                ['schedule' => $samplingSchedule->id],
                $this->printOptions($request),
            ))
            ->with('status', $this->extrasAddedMessage($quantity));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function printView(Request $request, array $data): View
    {
        $options = $this->printOptions($request);

        return view('lab.collection-qr.print', array_merge($data, [
            'layout' => $options['layout'],
            'copies' => $options['copies'],
            'companyName' => (string) (getActiveCompany()?->name ?? config('app.name')),
            'logoDataUri' => $this->resolveLogoDataUri(),
        ]));
    }

    private function resolveLogoDataUri(): string
    {
        $path = getActiveCompany()?->logo;
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }

        $relative = ltrim(preg_replace('#^/?storage/#', '', ltrim($path, '/')), '/');
        $fullPath = Storage::disk('public')->path($relative);

        if (! is_readable($fullPath)) {
            return '';
        }

        $contents = @file_get_contents($fullPath);
        if ($contents === false) {
            return '';
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * @return array{layout: string, copies: int}
     */
    private function printOptions(Request $request): array
    {
        $layout = (string) $request->input('layout', 'thermal');

        return [
            'layout' => in_array($layout, ['a4', 'thermal'], true) ? $layout : 'thermal',
            'copies' => max(1, min(10, (int) $request->input('copies', 1))),
        ];
    }

    private function findSchedule(string $schedule): SamplingSchedule
    {
        return SamplingSchedule::query()->visibleTo()->findOrFail($schedule);
    }

    private function extrasAddedMessage(int $quantity): string
    {
        return $quantity === 1 ? 'Added 1 extra QR code.' : "Added {$quantity} extra QR codes.";
    }

    private function userId(): ?string
    {
        $userId = auth()->id();

        return $userId !== null ? (string) $userId : null;
    }
}
