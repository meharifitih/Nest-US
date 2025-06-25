<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class ErrorHandlingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share error handling data with all views
        View::composer('*', function ($view) {
            $view->with('errorHandling', [
                'showDebugInfo' => config('app.debug'),
                'supportEmail' => config('mail.from.address', 'support@example.com'),
            ]);
        });

        // Custom Blade directive for error handling
        Blade::directive('errorHandler', function ($expression) {
            return "<?php echo app('App\\Traits\\ErrorHandler')->handleError($expression); ?>";
        });

        // Set up error logging configuration
        $this->configureErrorLogging();
    }

    /**
     * Configure error logging settings
     */
    protected function configureErrorLogging(): void
    {
        // Set up custom error logging channels if needed
        if (config('app.debug')) {
            Log::info('Error handling service provider loaded in debug mode');
        }
    }
} 