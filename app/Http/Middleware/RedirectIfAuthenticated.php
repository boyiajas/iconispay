<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $user = Auth::user();

                // Check if 2FA verification is required but not passed
                if ($user->google2fa_secret && !$request->session()->get('2fa_passed', false)) {
                    // If the user is trying to access 2FA verification, let it proceed
                    if ($request->routeIs('2fa.verify')) {
                        return $next($request);
                    }

                    // Redirect to 2FA verification if not passed
                    return redirect()->route('2fa.verify');
                }

                // If the user has already passed 2FA, check if they're trying to access the login or 2FA route
                if ($request->routeIs('login') || $request->routeIs('2fa.verify')) {
                    // Redirect to previous page or home
                    $previousUrl = url()->previous();
                    return redirect($previousUrl ?: route('home'));
                }

                // Check if the user does not have google2fa_secret set
                if (empty($user->google2fa_secret)) {
                    // Redirect to the 2FA setup route
                    //return redirect()->route('setup.2fa');
                }
                
                //return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
