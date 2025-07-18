<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ErrorLoggingService
{
    /**
     * Log database errors with user-friendly categorization
     */
    public static function logDatabaseError(\Exception $e, Request $request = null)
    {
        $context = [
            'type' => 'database_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => auth()->id(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
        ];

        // Categorize database errors
        if (strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
            $context['category'] = 'unique_constraint_violation';
            $context['user_friendly_message'] = 'Duplicate record detected';
        } elseif (strpos($e->getMessage(), 'SQLSTATE[23503]') !== false) {
            $context['category'] = 'foreign_key_violation';
            $context['user_friendly_message'] = 'Referenced record not found';
        } elseif (strpos($e->getMessage(), 'SQLSTATE[42P01]') !== false) {
            $context['category'] = 'table_not_found';
            $context['user_friendly_message'] = 'Database table missing';
        } elseif (strpos($e->getMessage(), 'SQLSTATE[42703]') !== false) {
            $context['category'] = 'column_not_found';
            $context['user_friendly_message'] = 'Database column missing';
        } else {
            $context['category'] = 'general_database_error';
            $context['user_friendly_message'] = 'Database operation failed';
        }

        Log::error('Database Error', $context);
    }

    /**
     * Log validation errors
     */
    public static function logValidationError(\Exception $e, Request $request = null)
    {
        $context = [
            'type' => 'validation_error',
            'message' => $e->getMessage(),
            'user_id' => auth()->id(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'input_data' => $request ? $request->except(['password', 'password_confirmation']) : null,
        ];

        Log::warning('Validation Error', $context);
    }

    /**
     * Log authentication errors
     */
    public static function logAuthenticationError(\Exception $e, Request $request = null)
    {
        $context = [
            'type' => 'authentication_error',
            'message' => $e->getMessage(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
        ];

        Log::warning('Authentication Error', $context);
    }

    /**
     * Log authorization errors
     */
    public static function logAuthorizationError(\Exception $e, Request $request = null)
    {
        $context = [
            'type' => 'authorization_error',
            'message' => $e->getMessage(),
            'user_id' => auth()->id(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
        ];

        Log::warning('Authorization Error', $context);
    }

    /**
     * Log general application errors
     */
    public static function logGeneralError(\Exception $e, Request $request = null)
    {
        $context = [
            'type' => 'general_error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
        ];

        Log::error('General Error', $context);
    }

    /**
     * Get user-friendly error message based on exception
     */
    public static function getUserFriendlyMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        // Database errors
        if (strpos($message, 'SQLSTATE[23505]') !== false) {
            if (strpos($message, 'users_email_unique') !== false) {
                return 'This email address is already registered. Please use a different email or try logging in.';
            } elseif (strpos($message, 'users_phone_number_unique') !== false) {
                return 'This phone number is already registered. Please use a different phone number.';
            } elseif (strpos($message, 'users_fayda_id_unique') !== false) {
                return 'This Fayda ID is already registered. Please use a different Fayda ID.';
            } else {
                return 'This record already exists. Please check for duplicates.';
            }
        } elseif (strpos($message, 'SQLSTATE[23503]') !== false) {
            return 'Cannot delete this record as it is referenced by other data.';
        } elseif (strpos($message, 'SQLSTATE[42P01]') !== false || strpos($message, 'SQLSTATE[42703]') !== false) {
            return 'System configuration error. Please contact support.';
        }

        // File upload errors
        if (strpos($message, 'file') !== false && strpos($message, 'upload') !== false) {
            return 'File upload failed. Please try again.';
        }

        // Network errors
        if (strpos($message, 'connection') !== false || strpos($message, 'timeout') !== false) {
            return 'Network connection error. Please check your internet connection and try again.';
        }

        // Default message
        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Check if error should be reported to external services
     */
    public static function shouldReportError(\Exception $e): bool
    {
        // Don't report validation errors
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return false;
        }

        // Don't report authentication errors
        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return false;
        }

        // Don't report authorization errors
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return false;
        }

        // Don't report not found errors
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return false;
        }

        // Don't report HTTP not found errors
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return false;
        }

        return true;
    }
} 