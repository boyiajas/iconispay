<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Brian2694\Toastr\Facades\Toastr;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

class TwoFactorAuthController extends Controller
{
    // Show the 2FA verification form
    public function show2faForm()
    {
        return view('auth.2fa'); // Ensure this view exists
    }

    // Verify the 2FA code
    public function verify2fa(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric',
        ]);

        $google2fa = app(Google2FA::class);
        $user = Auth::user();//dd($request->input('one_time_password'));

        // Use the google2fa_secret from the user model if it's set, otherwise use the session
        $secret = $user->google2fa_secret ?? session('google2fa_secret');

        try {
            // Verify the 2FA code
            $valid = $google2fa->verifyKey($secret, $request->input('one_time_password')); //dd($user->google2fa_secret);

            if ($valid) {

                // If the secret was in the session and not yet saved to the user model, save it now
                if (empty($user->google2fa_secret) && $secret === session('google2fa_secret')) {
                    $user->google2fa_secret = $secret;
                    $user->save();
                }

                // Mark the user as having passed 2FA
                $request->session()->put('2fa_passed', true);
                Toastr::success('2FA verification successful!', 'Success');

                // Redirect to the intended page or home
                return redirect()->intended('home');
            } else {
                Toastr::error('Invalid 2FA code. Please try again.', 'Error');
                return redirect()->route('2fa.verify');
            }
        } catch (SecretKeyTooShortException $e) { //dd($e);
            // Handle the SecretKeyTooShortException and display the error
            Toastr::error('Secret key is too short. Must be at least 16 base32 characters.', 'Error');
            return redirect()->route('2fa.verify');
        } catch (\Exception $e) {
            // Handle any other exceptions and display a generic error message
            Toastr::error('An unexpected error occurred. Please try again.', 'Error');
            return redirect()->route('2fa.verify');
        }
    }
}
