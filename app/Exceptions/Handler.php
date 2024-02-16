<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ValidationException $e, $request) {
            return $this->handleValidationException($e, $request);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Handle ValidationException
        if ($exception instanceof ValidationException) {
            $getError = $exception->errors();
            return $this->handleValidationException($exception, $request, $getError);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleValidationException(ValidationException $exception, $request)
    {
        $errors = $exception->errors();

        // Custom error message or response using your helper function
        $response = generateResponse(null, 'Validation failed', $errors, 422);

        return response()->json($response, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
