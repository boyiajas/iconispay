<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Observers\AuditTrailObserver;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        register as registration;
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'google2fa_secret' => $data['google2fa_secret'],
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $google2fa = app('pragmarx.google2fa'); 
        $registration_data = $request->all(); 
        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();

        $request->session()->put('registration_data', $registration_data); 

        $QR_Image = $google2fa->getQRCodeInline(

            config('app.name'),
            $registration_data['email'],
            $registration_data['google2fa_secret']

        );

        return view('google2fa.register', ['QR_Image' => $QR_Image, 'secret' => $registration_data['google2fa_secret']]);

    }
    public function completeRegistration(Request $request)
    {
        // Retrieve the registration data from the session
        $registration_data = session('registration_data');//dd($registration_data);

        // Check if the registration data exists and is an array
        if (is_array($registration_data)) {
            // Merge the registration data into the request
            $request->merge($registration_data);
        } else {
            AuditTrailObserver::logCustomAction('Error, Registration failed data is missing.', User::getModel(), null, $registration_data->toArray());
            // Handle the case where the registration data is missing
            return redirect()->route('register')->withErrors([
                'error' => 'Registration data is missing. Please try registering again.',
            ]);
            Toastr::error('Registration data is missing. Please try registering again.','Error');
        }

        // Continue with the registration process
        return $this->registration($request);
    }

    public function complete2FASetup()
    {
        /* // Retrieve the registration data from the session
        $registration_data = session('registration_data');//dd($registration_data);

        // Check if the registration data exists and is an array
        if (is_array($registration_data)) {
            // Merge the registration data into the request
            $request->merge($registration_data);
        } else {
            // Handle the case where the registration data is missing
            return redirect()->route('register')->withErrors([
                'error' => 'Registration data is missing. Please try registering again.',
            ]);

            Toastr::error('Registration data is missing. Please try registering again.','Error');
        } */

        // Continue with the registration process
        return redirect()->route('2fa.verify');
    }

     // Function for already registered users to set up Google 2FA
     public function redirectTo2FASetup()
     {
        $user = Auth::user(); //dd($user->google2fa_secret);
         // Check if the user does not have a google2fa_secret
        if (empty($user->google2fa_secret)) { //dd("we are here");

            $google2fa = app('pragmarx.google2fa'); 
            // $google2fa = app(Google2FA::class);
             $newSecret = $google2fa->generateSecretKey();
             // Store the new secret key in the session but do not update the user yet
             session(['google2fa_secret' => $newSecret]);
 
             // Generate the QR code for the new secret
             $QR_Image = $google2fa->getQRCodeInline(
                 config('app.name'),
                 $user->email,
                 $newSecret
             );
 
             Toastr::info('Please scan the QR code to complete your 2FA setup.', 'Info');
             
             // Redirect to the google2fa.register view with the QR code and secret
             return view('google2fa.setup', [
                 'QR_Image' => $QR_Image,
                 'secret' => $newSecret
             ]);
        }
 
         // If the user already has a google2fa_secret, redirect them to the home page
         return redirect()->route('home');
     }

}
