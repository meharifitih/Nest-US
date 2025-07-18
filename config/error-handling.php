<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for error handling and
    | user-friendly error messages.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Show Technical Errors
    |--------------------------------------------------------------------------
    |
    | Whether to show technical error details to users. In production,
    | this should always be false.
    |
    */
    'show_technical_errors' => env('SHOW_TECHNICAL_ERRORS', false),

    /*
    |--------------------------------------------------------------------------
    | Log Errors
    |--------------------------------------------------------------------------
    |
    | Whether to log errors for debugging purposes.
    |
    */
    'log_errors' => env('LOG_ERRORS', true),

    /*
    |--------------------------------------------------------------------------
    | User-Friendly Error Messages
    |--------------------------------------------------------------------------
    |
    | Custom error messages for different types of errors.
    |
    */
    'messages' => [
        'database' => [
            'unique_constraint' => [
                'email' => 'This email address is already registered. Please use a different email or try logging in.',
                'phone' => 'This phone number is already registered. Please use a different phone number.',
                'fayda_id' => 'This Fayda ID is already registered. Please use a different Fayda ID.',
                'default' => 'This record already exists. Please check for duplicates.',
            ],
            'foreign_key' => 'Cannot delete this record as it is referenced by other data.',
            'table_not_found' => 'System configuration error. Please contact support.',
            'column_not_found' => 'System configuration error. Please contact support.',
            'general' => 'A database error occurred. Please try again.',
        ],
        'validation' => [
            'general' => 'Please check your input and try again.',
            'required' => 'This field is required.',
            'email' => 'Please enter a valid email address.',
            'unique' => 'This value is already taken.',
        ],
        'authentication' => [
            'login_required' => 'Please log in to access this resource.',
            'invalid_credentials' => 'Invalid email or password.',
            'account_locked' => 'Your account has been locked. Please contact support.',
        ],
        'authorization' => [
            'permission_denied' => 'You do not have permission to perform this action.',
            'role_required' => 'You need specific permissions to access this resource.',
        ],
        'file' => [
            'upload_failed' => 'File upload failed. Please try again.',
            'invalid_file' => 'Invalid file type. Please check the file format.',
            'file_too_large' => 'File is too large. Please choose a smaller file.',
        ],
        'network' => [
            'connection_error' => 'Network connection error. Please check your internet connection and try again.',
            'timeout' => 'Request timed out. Please try again.',
        ],
        'general' => [
            'unexpected' => 'An unexpected error occurred. Please try again later.',
            'maintenance' => 'The system is currently under maintenance. Please try again later.',
            'not_found' => 'The requested resource was not found.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Reporting
    |--------------------------------------------------------------------------
    |
    | Configure which errors should be reported to external services.
    |
    */
    'reporting' => [
        'enabled' => env('ERROR_REPORTING_ENABLED', false),
        'exclude_types' => [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Pages
    |--------------------------------------------------------------------------
    |
    | Configuration for custom error pages.
    |
    */
    'pages' => [
        '404' => 'errors.404',
        '403' => 'errors.403',
        '500' => 'errors.500',
        'general' => 'errors.general',
    ],

    /*
    |--------------------------------------------------------------------------
    | Support Information
    |--------------------------------------------------------------------------
    |
    | Contact information to show on error pages.
    |
    */
    'support' => [
        'email' => env('SUPPORT_EMAIL', 'support@example.com'),
        'phone' => env('SUPPORT_PHONE', '+1234567890'),
        'website' => env('SUPPORT_WEBSITE', 'https://support.example.com'),
    ],
]; 