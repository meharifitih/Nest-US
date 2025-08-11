<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Avoid redirect loop: if already hitting auth pages, don't redirect again
            if ($request->is('login*') || $request->is('register*') || $request->is('password*')) {
                return null;
            }
            return route('login');
        }
    }
}
