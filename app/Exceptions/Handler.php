<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use PDOException;
use Throwable;
use Log;
use App\Services\ErrorLoggingService;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Use our error logging service for better categorization
            if ($e instanceof QueryException || $e instanceof PDOException) {
                ErrorLoggingService::logDatabaseError($e, request());
            } elseif ($e instanceof ValidationException) {
                ErrorLoggingService::logValidationError($e, request());
            } elseif ($e instanceof AuthenticationException) {
                ErrorLoggingService::logAuthenticationError($e, request());
            } elseif ($e instanceof AuthorizationException) {
                ErrorLoggingService::logAuthorizationError($e, request());
            } else {
                ErrorLoggingService::logGeneralError($e, request());
            }
        });

        // Handle database exceptions
        $this->renderable(function (QueryException $e, $request) {
            return $this->handleDatabaseException($e, $request);
        });

        // Handle PDO exceptions
        $this->renderable(function (PDOException $e, $request) {
            return $this->handleDatabaseException($e, $request);
        });

        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return $this->handleModelNotFoundException($e, $request);
        });

        // Handle validation exceptions
        $this->renderable(function (ValidationException $e, $request) {
            return $this->handleValidationException($e, $request);
        });

        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e, $request) {
            return $this->handleAuthenticationException($e, $request);
        });

        // Handle authorization exceptions
        $this->renderable(function (AuthorizationException $e, $request) {
            return $this->handleAuthorizationException($e, $request);
        });

        // Handle not found exceptions
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return $this->handleNotFoundException($e, $request);
        });

        // Handle method not allowed exceptions
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return $this->handleMethodNotAllowedException($e, $request);
        });

        // Handle HTTP exceptions
        $this->renderable(function (HttpException $e, $request) {
            return $this->handleHttpException($e, $request);
        });

        // Handle all other exceptions
        $this->renderable(function (Throwable $e, $request) {
            return $this->handleGenericException($e, $request);
        });
    }

    /**
     * Handle database exceptions
     */
    protected function handleDatabaseException($e, $request)
    {
        $message = ErrorLoggingService::getUserFriendlyMessage($e);
        $statusCode = 500;

        // Set appropriate status codes for different error types
        if (strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
            $statusCode = 422; // Unprocessable Entity for validation errors
        } elseif (strpos($e->getMessage(), 'SQLSTATE[23503]') !== false) {
            $statusCode = 422; // Unprocessable Entity for constraint violations
        }

        return $this->renderErrorResponse($message, $statusCode, $request);
    }

    /**
     * Handle model not found exceptions
     */
    protected function handleModelNotFoundException($e, $request)
    {
        $message = 'The requested item was not found.';
        return $this->renderErrorResponse($message, 404, $request);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException($e, $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException($e, $request)
    {
        $message = 'Please log in to access this resource.';
        return $this->renderErrorResponse($message, 401, $request);
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException($e, $request)
    {
        $message = 'You do not have permission to perform this action.';
        return $this->renderErrorResponse($message, 403, $request);
    }

    /**
     * Handle not found exceptions
     */
    protected function handleNotFoundException($e, $request)
    {
        $message = 'The requested page was not found.';
        return $this->renderErrorResponse($message, 404, $request);
    }

    /**
     * Handle method not allowed exceptions
     */
    protected function handleMethodNotAllowedException($e, $request)
    {
        $message = 'The requested method is not allowed.';
        return $this->renderErrorResponse($message, 405, $request);
    }

    /**
     * Handle HTTP exceptions
     */
    protected function handleHttpException($e, $request)
    {
        $message = 'An error occurred while processing your request.';
        return $this->renderErrorResponse($message, $e->getStatusCode(), $request);
    }

    /**
     * Handle generic exceptions
     */
    protected function handleGenericException($e, $request)
    {
        $message = 'An unexpected error occurred. Please try again later.';
        return $this->renderErrorResponse($message, 500, $request);
    }

    /**
     * Render error response based on request type
     */
    protected function renderErrorResponse($message, $statusCode, $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], $statusCode);
        }

        // For web requests, redirect to error page or back with error message
        if ($statusCode === 404) {
            return response()->view('errors.404', ['message' => $message], 404);
        }

        if ($statusCode >= 500) {
            return response()->view('errors.general', [
                'message' => $message,
                'statusCode' => $statusCode
            ], $statusCode);
        }

        // For other errors, redirect back with error message
        return redirect()->back()->with('error', $message);
    }
}
