<?php

namespace App\Exceptions;

use App\Exceptions\ValidationException as AppValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    function register()
    {
        $this->reportable(function (\Throwable $e) {
            //
        });
    }

    function render($request, \Throwable $e)
    {
        if ($request->is('api/*')) {
            return $this->processApiException($e);
        }
        return parent::render($request, $e);
    }

    private function processApiException(\Throwable $e): JsonResponse
    {
        $e = $this->prepareException($e);
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }
        return $this->apiResponse($e);
    }

    private function apiResponse(\Throwable $e): JsonResponse
    {
        $statusCode = 500;
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } else if ($e instanceof AuthenticationException) {
        } else if ($e instanceof ValidationException || $e instanceof AppValidationException) {
            $statusCode = 400;
        }

        $response = ['status' => $statusCode];

        $key = ($e instanceof AppValidationException) ? $e->key : null;
        $vars = ($e instanceof AppValidationException) ? $e->vars : null;

        switch ($statusCode) {
            case 401:
                if ($key === null) $key = 'common:error.unauthorized';
                $response['message'] = $e->getMessage() ?? 'Unauthenticated';
                break;
            case 403:
                if ($key === null) $key = 'common:error.forbidden';
                $response['message'] = $e->getMessage() ?? 'Forbidden';
                break;
            case 404:
                if ($key === null) $key = 'common:error.request-not-found';
                $response['message'] = $e->getMessage() ?? 'Not Found';
                break;
            case 405:
                $response['message'] = $e->getMessage() ?? 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $e->original['message'];
                $response['vars'] = $e->original['vars'];
                break;
            default:
                $response['message'] = $e->getMessage() ?? 'Whoops, looks like something went wrong';
                break;
        }
        if ($key === null && $e instanceof AppValidationException) {
            $key = 'common:error.validation-error';
        } else if ($e instanceof ValidationException) {
            $key = 'common:error.validation-error';
            $response['vars'] = $e->errors();
        }

        $response['key'] = $key ?? 'common:error.general';
        if ($vars !== null) $response['$vars'] = $vars;

        if (config('app.debug')) {
            $response['trace'] = simple_trace($e);
            $response['code'] = $e->getCode();
        }

        return response()->json($response, $statusCode, ['Access-Control-Allow-Origin' => '*']);
    }

}
