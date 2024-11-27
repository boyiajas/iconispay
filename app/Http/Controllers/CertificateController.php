<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function register(Request $request, User $user)
    {
        $request->validate([
            'certificate' => [
                'required',
                'max:2048', // Max file size: 2MB
                function ($attribute, $value, $fail) {
                    $uploadedFile = request()->file($attribute);
    
                    // Ensure the file has a ".pem" extension
                    if ($uploadedFile->getClientOriginalExtension() !== 'pem' || $uploadedFile->getClientOriginalExtension() !== 'crt') {
                        $fail('The certificate must be a .pem file.');
                    }
                },
            ],
        ]);

        
        $certificatePath = $request->file('certificate')->getPathname();
        $output = shell_exec("openssl x509 -in {$certificatePath} -noout -fingerprint -dates");
        
        preg_match('/Fingerprint=(.*)/', $output, $fingerprintMatch);
        preg_match('/notAfter=(.*)/', $output, $notAfterMatch);

        $fingerprint = $fingerprintMatch[1] ?? null;
        $notAfter = $notAfterMatch[1] ?? null; 

        if (!$fingerprint || !$notAfter) {
            return response()->json(['message' => 'Invalid certificate.'], 400);
        }

        try {
            // Parse the notAfter date correctly
            $expiresAt = \Carbon\Carbon::createFromFormat('M d H:i:s Y T', trim($notAfter));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid expiration date format.'], 400);
        }

        Certificate::create([
            'user_id' => $user->id,
            'certificate_hash' => $fingerprint,
            'expires_at' => $expiresAt,
        ]);

        return response()->json(['message' => 'Certificate registered successfully.']);
    }


    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        //
    }
}
