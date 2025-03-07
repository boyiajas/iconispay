<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\User;
use App\Observers\AuditTrailObserver;
use Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            AuditTrailObserver::logCustomAction('User successfully enter login credentials', $user, null, $user->toArray());
            /** the certificate request process start here */

            // Retrieve the client certificate from the request (via $_SERVER)
            $clientCert = $_SERVER['SSL_CLIENT_CERT'] ?? null; //dd($request);
           
            if ($clientCert) { 
                //dd($clientCert);

                try {

                     // Decode the client certificate (assumes PKCS#12 format)
                   
                    // Clean and reformat the certificate
                    $clientCert = str_replace(["\r", "\n", "\t"], '', $clientCert);
                    $clientCert = preg_replace('/(-----BEGIN CERTIFICATE-----)(.*)(-----END CERTIFICATE-----)/', "$1\n$2\n$3", $clientCert);
                    $clientCert = wordwrap($clientCert, 64, "\n", true);

                    // Verify the formatting
                    if (!preg_match('/-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----/s', $clientCert)) {
                        //dd("Invalid certificate format still ....God");
                        Log::info("Invalid certificate format");
                        return response()->json(['error' => 'Invalid certificate format'], 400);

                    }
                    
                    // Parse the certificate to extract details
                    $certInfo = openssl_x509_parse($clientCert);
                    if (!$certInfo) {
                       // dd("Invalid certificate unable to read ---> clientCert output ", $clientCert);
                       Log::info("Invalid certificate unable to read ---> clientCert output");
                       AuditTrailObserver::logCustomAction('Invalid certificate unable to read', $user, null, $email);
                        return response()->json(['error' => 'Invalid certificate'], 400);
                    }
                    
                    // Optional debug: check the cleaned certificate format
                   
                    // Extract the certificate fingerprint
                    $rawFingerprint = openssl_x509_fingerprint($clientCert, 'sha1'); 
                  
                    if (!$rawFingerprint) {
                        Log::info("Invalid certificate fingerprint");
                        AuditTrailObserver::logCustomAction('Invalid certificate fingerprint', $user, null, $email);
                        throw new Exception('Invalid certificate fingerprint');
                    }

                    // Format the fingerprint to colon-separated hex (matches `generateClientCertificate`)
                    $fingerprint = strtoupper(implode(':', str_split($rawFingerprint, 2))); //dd($fingerprint);

                     // Validate the fingerprint against the database
                    $certificate = Certificate::where('user_id', $user->id)
                        ->where('certificate_hash', $fingerprint)
                        ->first();//dd($certificate);
                        //$user->syncRoles(['authoriser','bookkeeper']);

                        //dd("1", $user->hasRole('admin'), $certificate, $fingerprint);
                    if ((!$certificate || ($certificate->expires_at < now())) && !$user->hasRole('superadmin')) {// dd("1", $user->hasRole('admin'), $certificate, $fingerprint);
                        Log::info("Save roles, downgrade to 'user', and redirect");
                        AuditTrailObserver::logCustomAction('No certificate provided, downgrade to only user role', $user, null, $email);
                        if(empty($user->user_roles)){ //this is to avoid overwriting the roles already saved again
                            $roles = $user->roles->pluck('name')->toArray();
                            $user->update(['user_roles' => json_encode($roles)]);
                            $user->syncRoles(['user']);
                        }
                        //Log::info($certificate);
                        
                        
                    }else if (!empty($user->user_roles)) { //dd("2"); 
                        Log::info('If user_roles is not null, assign the roles back to the user');
                        AuditTrailObserver::logCustomAction('Certificate provided, upgrade and assign the roles back to the user', $user, null, json_decode($user->user_roles, true));
                        $roles = json_decode($user->user_roles, true);
                        //$user->syncRoles(['authoriser','bookkeeper']); //dd($user->roles->pluck('name')->toArray());
                        $user->syncRoles($roles);
                        $user->update(['user_roles' => null]);
                        //dd($roles, $user->roles->pluck('name')->toArray());
                    }

                    //dd("finger print successfully extracted ", $certInfo, $fingerprint);
                } catch (Exception $e) {
                    //dd($e->getMessage());
                    return redirect('login')->withErrors(['certificate' => $e->getMessage()]);
                }

            }else if($user->hasRole('superadmin')){
               //Log::info('we are here admin');
            }else{ //dd("we are here no certificate ");
                //Log::info("No client certificate, save current roles to user_roles and assign 'user' role");
                AuditTrailObserver::logCustomAction('No Certificate provided, downgrade to only user role', $user, null, $user->roles->pluck('name')->toArray());
                if(empty($user->user_roles)){ //this is to avoid overwriting the roles already saved again
                    $roles = $user->roles->pluck('name')->toArray();
                    $user->update(['user_roles' => json_encode($roles)]);
                    $user->syncRoles(['user']);
                }
            }

            /** the certificate request process ends here */
            //dd('we are here');
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
            Session::put('organisation_id', $user->organisation_id);

            //Toastr::success('Login successfully :)','Success');
            AuditTrailObserver::logCustomAction('User Login successfully', $user, null, $email);

            // Check if the user has set up 2FA
            if (empty($user->google2fa_secret)) {
                // Redirect to the 2FA setup page if 2FA is not set up
                return redirect()->route('setup.2fa'); 
            }
            // Redirect to the 2FA verification page if 2FA is set up
            return redirect()->route('2fa.verify'); 
            
            //return redirect()->intended('home');

        } else {
            Toastr::error('fail, WRONG USERNAME OR PASSWORD :)','Error');
            AuditTrailObserver::logCustomAction('Login failed, wrong username or password', User::getModel(), null, $email);
            return redirect('login');
        }
    }

    public function login()
    {
        return view('auth.login');
    }

    public function logout()
    {
        AuditTrailObserver::logCustomAction('Logout successfully', User::getModel(), null, Auth::user()->email);
        Auth::logout();
        
        Toastr::success('Logout successfully :)','Success');
        
        return redirect('login');
    }
}
