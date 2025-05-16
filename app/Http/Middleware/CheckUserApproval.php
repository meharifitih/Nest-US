<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserApproval
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Allow super admin to access everything
            if ($user->type === 'super admin') {
                return $next($request);
            }
            
            // Allow non-owner users (tenants, maintainers) to access everything
            if (!in_array($user->type, ['owner', 'super admin'])) {
                return $next($request);
            }
            
            // For owner users, check approval status
            if ($user->approval_status !== 'approved') {
                // Allow access to payment/subscription/review/logout routes
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
                ];
                if (in_array($request->route()->getName(), $allowedRoutes)) {
                    return $next($request);
                }
                // Redirect to review page for all other routes
                return redirect()->route('account.review');
            }
        }
        
        return $next($request);
    }
} 