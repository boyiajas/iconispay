<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    //use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
        //$this->middleware('auth')->only('logout');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $email    = $request->email;
        $password = $request->password;

        if (Auth::attempt(['email'=>$email,'password'=>$password])) {
            $user = Auth::user();
            /** the certificate request process start here */

            // Retrieve the client certificate from the request (via $_SERVER)
            $clientCert = $_SERVER['SSL_CLIENT_CERT'] ?? null; //dd($request);
           
            if ($clientCert) { 
                // Extract the certificate fingerprint
                $fingerprint = openssl_x509_fingerprint($clientCert);

                // Validate the fingerprint against the database
                $certificate = Certificate::where('user_id', $user->id)
                    ->where('certificate_hash', $fingerprint)
                    ->first();

                if (!$certificate) {
                    //Auth::logout(); // Log out the user
                    //update the user role to a normal user
                    //return redirect('login')->withErrors(['certificate' => 'Invalid client certificate.']);
                    // Remove all roles and assign only 'user' role
                    //$user->syncRoles(['user']);
                }

                // Optional: Check if the certificate is expired
                if ($certificate->expires_at < now()) {
                    //Auth::logout();
                    //return redirect('login')->withErrors(['certificate' => 'Client certificate has expired.']);
                    //update to user role to a normal user 
                    // Remove all roles and assign only 'user' role
                    //$user->syncRoles(['user']);
                }

            }else if($user->hasRole('admin')){
               //dd('we are here');
            }else{
                 //update the user role to a normal user
                // Remove all roles and assign only 'user' role
                //$user->syncRoles(['user']);
            }

            /** the certificate request process ends here */

            /** last login updage*/
            $lastUpdate = [
                'last_login' => Carbon::now(),
            ];
            User::where('email',$email)->update($lastUpdate);
            /** get session */
            $user = Auth::User();
            Session::put('name', $user->name);
            Session::put('email', $user->email);
            Session::put('user_id', $user->user_id);
            //Session::put('join_date', $user->join_date);
            Session::put('last_login', $user->last_login);
            Session::put('phone_number', $user->phone_number);
            Session::put('status', $user->status);
            //Session::put('role_name', $user->role_name);
            Session::put('avatar', $user->avatar);
            Session::put('position', $user->position);
            Session::put('department', $user->department);

            //Toastr::success('Login successfully :)','Success');

            // Check if the user has set up 2FA
            if (empty($user->google2fa_secret)) {
                // Redirect to the 2FA setup page if 2FA is not set up
                return redirect()->route('setup.2fa'); 
            } else {  
                // Redirect to the 2FA verification page if 2FA is set up
                return redirect()->route('2fa.verify'); 
            }

           /*  if(Auth::user()->hasRole('user')){
                return redirect()->intended('customer/dashboard');
            } */
           
            //return redirect()->intended('home');

        } else {
            Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
            return redirect('login');
        }
    }

    public function login()
    {
        return view('auth.login');
    }

    public function logout()
    {
        Auth::logout();
        Toastr::success('Logout successfully :)','Success');
        
        return redirect('login');
    }
}
