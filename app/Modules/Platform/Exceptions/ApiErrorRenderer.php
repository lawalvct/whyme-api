<?php

namespace App\Modules\Platform\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Renders every API error in one shape: { code, message, fields, retryable }.
 * Clients translate by `code`; `message` is in the request's language.
 */
class ApiErrorRenderer
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        [$code, $status, $retryable, $fields, $data, $headers] = match (true) {
            $e instanceof ApiException => [$e->errorCode, $e->status, $e->retryable, $e->fields, $e->data, $e->headers],
            $e instanceof ValidationException => ['validation_failed', 422, false, $e->errors(), null, []],
            $e instanceof AuthenticationException => ['unauthenticated', 401, false, [], null, []],
            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException => ['forbidden', 403, false, [], null, []],
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => ['not_found', 404, false, [], null, []],
            $e instanceof ThrottleRequestsException => ['too_many_requests', 429, true, [], null, $e->getHeaders()],
            $e instanceof HttpExceptionInterface => ['http_'.$e->getStatusCode(), $e->getStatusCode(), $e->getStatusCode() >= 500, [], null, $e->getHeaders()],
            default => ['server_error', 500, true, [], null, []],
        };

        $body = [
            'code' => $code,
            'message' => self::message($code),
            'fields' => (object) $fields,
            'retryable' => $retryable,
        ];

        if ($data !== null) {
            $body += $data;
        }

        if ($code === 'server_error' && config('app.debug')) {
            $body['debug'] = ['exception' => $e::class, 'message' => $e->getMessage()];
        }

        return new JsonResponse($body, $status, $headers);
    }

    private static function message(string $code): string
    {
        return Lang::has("errors.$code") ? __("errors.$code") : __('errors.server_error');
    }
}
