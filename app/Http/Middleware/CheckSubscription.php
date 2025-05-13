<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Allow super admin to access everything
            if ($user->type === 'super admin') {
                return $next($request);
            }

            // Define allowed routes for unapproved users
            $allowedRoutes = [
                'subscriptions.index',
                'subscriptions.show',
                'subscriptions.store',
                'subscriptions.subscribe',
                'subscription.bank.transfer',
                'subscription.stripe.payment',
                'subscription.paypal',
                'subscription.flutterwave',
                'payment.verification.upload',
                'logout',
                'profile.edit',
                'profile.update',
                'password.update',
                'account.review',
                // Add any other payment/upload related routes here
            ];

            $currentRoute = $request->route() ? $request->route()->getName() : null;
            \Log::info('CheckSubscription middleware: current route = ' . $currentRoute);

            // Only block access to non-allowed routes if not approved
            if ($user->approval_status !== 'approved') {
                if (!in_array($currentRoute, $allowedRoutes)) {
                    return redirect()->route('account.review')
                        ->with('error', __('Your account is pending approval. Please select and pay for a subscription.'));
                }
            }
        }

        return $next($request);
    }
} 