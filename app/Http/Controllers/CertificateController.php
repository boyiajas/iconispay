<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $certificateDirectory = storage_path('app/certificates'); // Define certificate directory

        // Ensure the directory exists
        if (!is_dir($certificateDirectory)) {
            mkdir($certificateDirectory, 0755, true); // Create directory with appropriate permissions
        }

        $uploadedFile = $request->file('certificate');

        // Store the uploaded file securely in storage
        $certificatePath = $uploadedFile->storeAs('certificates', $uploadedFile->getClientOriginalName());

        //$absolutePath = Storage::path($certificatePath);

        // Extract fingerprint and expiration date using OpenSSL
        $absolutePath = Storage::path($certificatePath);
        $output = shell_exec("openssl x509 -in {$absolutePath} -noout -fingerprint -dates 2>&1");

        // Check for errors in OpenSSL output
        if (empty($output)) {
            Storage::delete($certificatePath);
            return response()->json(['message' => 'Failed to parse the certificate.'], 400);
        }


        
        //$certificatePath = $request->file('certificate')->getPathname();
        //$output = shell_exec("openssl x509 -in {$certificatePath} -noout -fingerprint -dates");
        
        preg_match('/Fingerprint=(.*)/', $output, $fingerprintMatch);
        preg_match('/notAfter=(.*)/', $output, $notAfterMatch);

        $fingerprint = $fingerprintMatch[1] ?? null;
        $notAfter = $notAfterMatch[1] ?? null; 

        if (!$fingerprint || !$notAfter) {
            Storage::delete($certificatePath);
            return response()->json(['message' => 'Invalid certificate.'], 400);
        }

        try {
            // Parse the notAfter date correctly
            $expiresAt = Carbon::createFromFormat('M d H:i:s Y T', trim($notAfter));
        } catch (\Exception $e) {
            Storage::delete($certificatePath); // Cleanup invalid file
            return response()->json(['message' => 'Invalid expiration date format.'], 400);
        }

        Certificate::create([
            'user_id' => $user->id,
            'certificate_hash' => $fingerprint,
            'expires_at' => $expiresAt,
            'file_path' => $certificatePath,
        ]);

        return response()->json(['message' => 'Certificate registered successfully.']);
    }

    public function downloadCertificate($certificateId)
    {
        // Get the currently authenticated user ID
        $user = Auth::user();
         
        // Check if the user has an 'admin' or 'authorizer' role
        if ($user->hasRole('admin') || $user->hasRole('authoriser')) {
            // Serve the file using Storage, making sure it's only accessible to the authenticated user
            $certificate = Certificate::findOrFail($certificateId);

            $filePath = storage_path('app/' . $certificate->file_path);

            if (!file_exists($filePath)) {
                return response()->json(['message' => 'Certificate file not found.'], 404);
            }

            return response()->download($filePath, "client_certificate_{$certificate->user_id}.p12");

        }
            
        abort(403, 'Unauthorized access to the file');
           
        
    }

    public function generateClientCertificateOld($userId)
    {
        // Paths for CA files
        $caKeyPath = storage_path('app/payiconis.key');
        $caCertPath = storage_path('app/payiconis.crt');
        $certDir = storage_path('app/certificates');

        // Generate a timestamped directory for this certificate
        $timestamp = now()->format('Ymd_His');
        $certSubDir = "{$certDir}/{$timestamp}";

        // Ensure the directory exists
        if (!is_dir($certSubDir)) {
            mkdir($certSubDir, 0755, true);
        }

        // Fetch the user
        $user = User::findOrFail($userId);

        // Generate unique filenames for the client's key, CSR, and certificate
        $keyPath = "{$certSubDir}/user_{$user->id}.key";
        $csrPath = "{$certSubDir}/user_{$user->id}.csr";
        $crtPath = "{$certSubDir}/user_{$user->id}.crt";
        $p12Path = "{$certSubDir}/user_{$user->id}.p12";

        // Generate private key
        shell_exec("openssl genrsa -out {$keyPath} 2048");

        // Generate Certificate Signing Request (CSR)
        $subject = "/CN={$user->name}";
        shell_exec("openssl req -new -key {$keyPath} -out {$csrPath} -subj \"{$subject}\"");

        // Generate the client certificate signed by the CA
        shell_exec("openssl x509 -req -in {$csrPath} -CA {$caCertPath} -CAkey {$caKeyPath} -CAcreateserial -out {$crtPath} -days 365 -sha256");

        // Bundle the client certificate and private key into a .p12 file
        shell_exec("openssl pkcs12 -export -out {$p12Path} -inkey {$keyPath} -in {$crtPath} -name \"Client Certificate\" -password pass:secret");

        // Extract certificate fingerprint and expiration date
        $output = shell_exec("openssl x509 -in {$crtPath} -noout -fingerprint -dates");
        
        preg_match('/Fingerprint=(.*)/', $output, $fingerprintMatch);
        preg_match('/notAfter=(.*)/', $output, $notAfterMatch);

        $fingerprint = $fingerprintMatch[1] ?? null;
        $notAfter = $notAfterMatch[1] ?? null;

        if (!$fingerprint || !$notAfter) {
            throw new \Exception('Failed to parse certificate details.');
        }

        // Convert expiration date to Carbon
        $expiresAt = Carbon::createFromFormat('M d H:i:s Y T', trim($notAfter));

        // Save the certificate in the database
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'certificate_hash' => $fingerprint,
            'expires_at' => $expiresAt,
            'file_path' => "certificates/{$timestamp}/user_{$user->id}.p12", // Relative path for download
        ]);

        return $certificate;
    }

    public function generateClientCertificate($userId)
{
    // Paths for CA files
    $caKeyPath = storage_path('app/payiconis.key');
    $caCertPath = storage_path('app/payiconis.crt');
    $certDir = storage_path('app/certificates');

    // Generate a timestamped directory for this certificate
    $timestamp = now()->format('Ymd_His');
    $certSubDir = "{$certDir}/{$timestamp}";

    // Ensure the directory exists
    if (!is_dir($certSubDir)) {
        mkdir($certSubDir, 0755, true);
    }

    // Fetch the user
    $user = User::findOrFail($userId);

    // Generate unique filenames for the client's key, CSR, and certificate
    $keyPath = "{$certSubDir}/user_{$user->id}.key";
    $csrPath = "{$certSubDir}/user_{$user->id}.csr";
    $crtPath = "{$certSubDir}/user_{$user->id}.crt";
    $p12Path = "{$certSubDir}/user_{$user->id}.p12";

    // Generate private key
    shell_exec("openssl genrsa -out {$keyPath} 2048");
    Log::info("Private key generated at: {$keyPath}");

    // Generate Certificate Signing Request (CSR)
    $subject = "/CN={$user->name}";
    shell_exec("openssl req -new -key {$keyPath} -out {$csrPath} -subj \"{$subject}\"");
    Log::info("CSR generated at: {$csrPath}");

    // Generate the client certificate signed by the CA
    shell_exec("openssl x509 -req -in {$csrPath} -CA {$caCertPath} -CAkey {$caKeyPath} -CAcreateserial -out {$crtPath} -days 365 -sha256");
    Log::info("Client certificate generated at: {$crtPath}");

    // Bundle the client certificate and private key into a .p12 file
    shell_exec("openssl pkcs12 -export -out {$p12Path} -inkey {$keyPath} -in {$crtPath} -name \"{$user->name}\" -password pass:secret");
    Log::info("P12 bundle generated at: {$p12Path}");

    // Extract certificate fingerprint and expiration date
    $output = shell_exec("openssl x509 -in {$crtPath} -noout -fingerprint -dates");
    Log::info("Certificate details: {$output}");

    // Parse the fingerprint and expiration date
    preg_match('/Fingerprint=(.*)/', $output, $fingerprintMatch);
    preg_match('/notAfter=(.*)/', $output, $notAfterMatch);

    $rawFingerprint = $fingerprintMatch[1] ?? null; // Example: "DF:C9:76:99:..."
    $notAfter = $notAfterMatch[1] ?? null;

    if (!$rawFingerprint || !$notAfter) {
        throw new \Exception('Failed to parse certificate details.');
    }

    // Format the fingerprint to ensure consistency (uppercase and colon-separated)
    $formattedFingerprint = strtoupper($rawFingerprint);

    // Convert expiration date to Carbon
    $expiresAt = Carbon::createFromFormat('M d H:i:s Y T', trim($notAfter));

    // Save the certificate in the database
    $certificate = Certificate::create([
        'user_id' => $user->id,
        'certificate_hash' => $formattedFingerprint, // Ensure colon-separated uppercase hash
        'expires_at' => $expiresAt,
        'file_path' => "certificates/{$timestamp}/user_{$user->id}.p12", // Relative path for download
    ]);

    return $certificate;
}

public function deleteClientCertificate($userId)
{
    // Fetch the user's certificate record from the Certificate model
    $certificate = Certificate::where('user_id', $userId)->first();

    if (!$certificate) {
        return response()->json(['message' => 'Certificate not found for the user.'], 404);
    }

    // Get the directory containing the certificate files
    $filePath = storage_path('app/' . $certificate->file_path); // Path to the .pem file
    $certDir = dirname($filePath); // Parent directory of the certificate files

    // List of expected file extensions
    $expectedFiles = ['key', 'csr', 'crt', 'p12'];

    // Delete the files
    foreach ($expectedFiles as $extension) {
        $file = "{$certDir}/user_{$userId}.{$extension}";
        if (File::exists($file)) {
            File::delete($file);
        }
    }

    // Check if the directory is empty and remove it
    if (File::isDirectory($certDir) && count(File::files($certDir)) === 0) {
        File::deleteDirectory($certDir);
    }

    // Delete the certificate record from the database
    $certificate->delete();

    return response()->json(['message' => 'Certificate and associated files deleted successfully.'], 200);
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
