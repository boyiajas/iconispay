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
    public function handleOld(Request $request, Closure $next, string ...$guards): Response
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

    /* public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();

                // Check if 2FA verification is required but not passed
                if ($user->google2fa_secret && !$request->session()->get('2fa_passed', false)) {
                    // Allow access to the 2FA verification page
                    if ($request->routeIs('2fa.verify')) {
                        return $next($request);
                    }

                    // Redirect to 2FA verification page
                    return redirect()->route('2fa.verify');
                }

                // Redirect authenticated users away from login or 2FA verification pages
                if ($request->routeIs('login') || $request->routeIs('2fa.verify')) {
                    return redirect()->intended(route('home'));
                }

                // Redirect to 2FA setup if google2fa_secret is not set
                if (empty($user->google2fa_secret)) {
                    return redirect()->route('setup.2fa');
                }

                // Default redirection for authenticated users
                return redirect(route('home'));
            }
        }

        return $next($request);
    } */
}
