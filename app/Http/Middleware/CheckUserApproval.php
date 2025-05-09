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
            
            // Allow non-owner users to access everything
            if ($user->type !== 'owner') {
                return $next($request);
            }
            
            // For owner users, check approval status
            if ($user->approval_status !== 'approved') {
                // Allow access to review page and logout
                if ($request->route()->getName() === 'account.review' || 
                    $request->route()->getName() === 'logout') {
                    return $next($request);
                }
                
                // Redirect to review page for all other routes
                return redirect()->route('account.review');
            }
        }
        
        return $next($request);
    }
} 