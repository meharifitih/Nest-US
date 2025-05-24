<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckOwnerDocuments
{
    public function handle(Request $request, Closure $next)
    {
        if (
            Auth::check() &&
            Auth::user()->type === 'owner' &&
            empty(Auth::user()->business_license)
        ) {
            // Allow access to the settings/profile page so user can upload
            if (!$request->is('settings') && !$request->is('settings/*')) {
                return redirect()->route('setting.index', ['tab' => 'user_profile_settings'])
                    ->with('error', __('Please upload your business license to continue using the system.'));
            }
        }

        return $next($request);
    }
} 