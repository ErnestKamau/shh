<?php

namespace App\Exceptions;

use App\Exceptions\Api\Portal\PortalApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isPortalSubmissionsApiRequest($request)) {
            if ($exception instanceof PortalApiException) {
                return $exception->render($request);
            }

            if ($exception instanceof ValidationException) {
                return $this->portalValidationResponse($exception);
            }

            if ($exception instanceof HttpExceptionInterface) {
                return $this->portalHttpExceptionResponse($exception);
            }
        }

        return parent::render($request, $exception);
    }

    private function isPortalSubmissionsApiRequest(Request $request): bool
    {
        return $request->is('api/v1/portal/submissions', 'api/v1/portal/submissions/*');
    }

    private function portalValidationResponse(ValidationException $exception): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'validation_failed',
                'message' => $exception->getMessage(),
                'fields' => $exception->errors(),
            ],
        ], $exception->status);
    }

    private function portalHttpExceptionResponse(HttpExceptionInterface $exception): \Illuminate\Http\JsonResponse
    {
        $status = $exception->getStatusCode();
        $message = $exception->getMessage() ?: match ($status) {
            401 => 'Authentication failed. Check your portal gateway API key.',
            403 => 'You do not have permission to perform this action.',
            404 => 'The requested resource was not found.',
            405 => 'This HTTP method is not allowed for this endpoint.',
            429 => 'Too many requests. Please try again later.',
            default => 'Something went wrong. Please try again.',
        };

        return response()->json([
            'error' => [
                'code' => 'http_'.$status,
                'message' => $message,
            ],
        ], $status);
    }
}
