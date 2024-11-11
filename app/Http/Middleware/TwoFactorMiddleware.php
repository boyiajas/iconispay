<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Check if 2FA has been passed
            /* if (!$request->session()->has('2fa_passed') || !$request->session()->get('2fa_passed')) { //dd('as been passed');
                // Redirect to the 2FA verification page
                return redirect()->route('2fa.verify');
            } */
            $user = Auth::user();

            // If the current route is '2fa.verify' or 'complete.registration', skip the checks
            if ($request->routeIs('2fa.verify') || $request->routeIs('complete.registration')) {
                return $next($request);
            }

            // Check if the user has not set up a google2fa_secret
            if (is_null($user->google2fa_secret)) {
                // Redirect to the complete registration route
                return redirect()->route('setup.2fa');
            }

            // Check if 2FA has not been passed
            if (!$request->session()->has('2fa_passed') || !$request->session()->get('2fa_passed')) {
                // Redirect to the 2FA verification page
                return redirect()->route('2fa.verify');
            }

            //dd('no nothing yet');
        }

        // Allow the request to proceed
        return $next($request);
    }
}
