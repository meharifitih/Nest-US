/**
 * Global Error Handler for Frontend
 * Handles JavaScript errors and provides user-friendly error messages
 */

class ErrorHandler {
    constructor() {
        this.init();
    }

    init() {
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError('Promise Rejection', event.reason);
            event.preventDefault();
        });

        // Handle JavaScript errors
        window.addEventListener('error', (event) => {
            this.handleError('JavaScript Error', event.error || event.message);
            event.preventDefault();
        });

        // Handle AJAX errors
        this.setupAjaxErrorHandling();
    }

    setupAjaxErrorHandling() {
        // Override fetch to handle errors
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                if (!response.ok) {
                    this.handleHttpError(response);
                }
                return response;
            } catch (error) {
                this.handleError('Network Error', error);
                throw error;
            }
        };

        // Handle jQuery AJAX errors if jQuery is available
        if (typeof $ !== 'undefined') {
            $(document).ajaxError((event, xhr, settings, error) => {
                this.handleAjaxError(xhr, settings, error);
            });
        }
    }

    handleError(type, error) {
        console.error(`${type}:`, error);

        // Log error to server if possible
        this.logErrorToServer(type, error);

        // Show user-friendly error message
        this.showErrorMessage(this.getUserFriendlyMessage(type, error));
    }

    handleHttpError(response) {
        const errorMessage = this.getHttpErrorMessage(response.status);
        this.showErrorMessage(errorMessage);
    }

    handleAjaxError(xhr, settings, error) {
        let message = 'An error occurred while processing your request.';
        
        if (xhr.status === 422) {
            // Validation errors
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.errors) {
                    message = this.formatValidationErrors(response.errors);
                }
            } catch (e) {
                message = 'Please check your input and try again.';
            }
        } else if (xhr.status === 404) {
            message = 'The requested resource was not found.';
        } else if (xhr.status === 403) {
            message = 'You do not have permission to perform this action.';
        } else if (xhr.status === 500) {
            message = 'Server error. Please try again later.';
        }

        this.showErrorMessage(message);
    }

    getUserFriendlyMessage(type, error) {
        switch (type) {
            case 'Network Error':
                return 'Network connection error. Please check your internet connection and try again.';
            case 'JavaScript Error':
                return 'A technical error occurred. Please refresh the page and try again.';
            case 'Promise Rejection':
                return 'An operation failed. Please try again.';
            default:
                return 'An unexpected error occurred. Please try again.';
        }
    }

    getHttpErrorMessage(status) {
        const messages = {
            400: 'Bad request. Please check your input.',
            401: 'Authentication required. Please log in.',
            403: 'Access denied. You do not have permission.',
            404: 'The requested resource was not found.',
            422: 'Validation failed. Please check your input.',
            429: 'Too many requests. Please wait a moment and try again.',
            500: 'Server error. Please try again later.',
            502: 'Bad gateway. Please try again later.',
            503: 'Service unavailable. Please try again later.',
            504: 'Gateway timeout. Please try again later.'
        };

        return messages[status] || 'An error occurred. Please try again.';
    }

    formatValidationErrors(errors) {
        if (typeof errors === 'object') {
            return Object.values(errors).flat().join('\n');
        }
        return 'Please check your input and try again.';
    }

    showErrorMessage(message) {
        // Remove existing error messages
        this.removeExistingMessages();

        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show global-error';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add to page
        const container = this.findBestContainer();
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
        }

        // Auto-hide after 10 seconds
        setTimeout(() => {
            this.removeMessage(errorDiv);
        }, 10000);
    }

    findBestContainer() {
        // Try to find the best container for error messages
        const selectors = [
            '.container',
            '.main-content',
            'main',
            '.content',
            'body'
        ];

        for (const selector of selectors) {
            const element = document.querySelector(selector);
            if (element) {
                return element;
            }
        }

        return document.body;
    }

    removeExistingMessages() {
        const existingMessages = document.querySelectorAll('.global-error');
        existingMessages.forEach(message => this.removeMessage(message));
    }

    removeMessage(messageElement) {
        if (messageElement && messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
    }

    logErrorToServer(type, error) {
        // Send error to server for logging
        const errorData = {
            type: type,
            message: error.message || error.toString(),
            stack: error.stack,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        };

        // Use fetch to send error data
        fetch('/api/log-error', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(errorData)
        }).catch(() => {
            // Silently fail if error logging fails
        });
    }
}

// Initialize error handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.errorHandler = new ErrorHandler();
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.errorHandler = new ErrorHandler();
    });
} else {
    window.errorHandler = new ErrorHandler();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ErrorHandler;
} 