<?php

use App\Exports\SimpleExport;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\AvsController;
use App\Http\Controllers\BeneficiaryAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmailPreviewController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FirmAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\MatterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PaymentController;



use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectIfAuthenticated;


use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;









/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//build email
Route::get('/email-preview/signatory', [EmailPreviewController::class, 'signatoryNotificationPreview']);
Route::get('/unsubscribe/{encoded}', function ($encoded) {
    return 'Unsubscribe Link with encoded data: ' . $encoded;
})->name('unsubscribe');
Route::get('/preferences/{encoded}', function ($encoded) {
    return 'Preferences Link with encoded data: ' . $encoded;
})->name('preferences');

Route::post('/api/avs-callback', [AvsController::class, 'handleAvsCallback']);

/* Route::post('/api/avs-callback', function (Request $request) { 
    // Basic authentication
    $username = $request->header('PHP_AUTH_USER');
    $password = $request->header('PHP_AUTH_PW');

    // Replace these with your desired credentials
    $validUsername = 'iconis';
    $validPassword = 'test123';

    if ($username !== $validUsername || $password !== $validPassword) {
        $response = ['error' => 'Unauthorized'];
        Log::info('Callback unauthorized access attempt', ['username' => $username]);

        return response()->json($response, 401);
    }

    // Process the request
    $data = $request->all();
    $accountNumber = $data['Response']['accountNumber'] ?? null;

    if ($accountNumber) {
        $beneficiaryAccount = BeneficiaryAccount::where('account_number', $accountNumber)->first();

        if ($beneficiaryAccount) {
            $beneficiaryAccount->update([
                'verified' => true,
                'verification_status' => 'successful',
                'account_found' => $data['Response']['accountExists'],
                'account_open' => $data['Response']['accountOpen'],
                'account_type_verified' => $data['Response']['accountType'],
                'account_type_match' => $data['Response']['accountTypeValid'],
                'branch_code_match' => '00',
                'holder_name_match' => $data['Response']['lastNameMatch'],
                'holder_initials_match' => $data['Response']['initialMatch'],
                'registration_number_match' => '00',
                'avs_verified_at' => now(),
            ]);
        } else {
            $firmAccount = FirmAccount::where('account_number', $accountNumber)->first();

            if ($firmAccount) {
                $firmAccount->update([
                    'verified' => true,
                    'verification_status' => 'successful',
                    'account_found' => $data['Response']['accountExists'],
                    'account_open' => $data['Response']['accountOpen'],
                    'account_type_verified' => $data['Response']['accountType'],
                    'account_type_match' => $data['Response']['accountTypeValid'],
                    'branch_code_match' => '00',
                    'holder_name_match' => $data['Response']['lastNameMatch'],
                    'holder_initials_match' => $data['Response']['initialMatch'],
                    'registration_number_match' => '00',
                    'avs_verified_at' => now(),
                ]);
            }
        }
    }

    // Prepare the response
    $response = [
        'message' => 'Callback received',
        'data' => $data
    ];

    // Log the response
    Log::info('Callback response', ['response' => $response]);

    return response()->json($response, 200);
}); */

Route::group(['middleware' => 'no_cache'], function (){


    Route::get('/', function () {
        return view('welcome');
    });



    Route::get('/complete-registration', [RegisterController::class, 'completeRegistration'])->name('complete.registration');
    Route::get('/complete-2fa-setup', [RegisterController::class, 'complete2FASetup'])->name('complete.2fa.setup');
    Route::post('/verify-2fa', [TwoFactorAuthController::class, 'verify2fa'])->name('verify.2fa');
    Route::get('/setup-2fa', [RegisterController::class, 'redirectTo2FASetup'])->name('setup.2fa')->middleware(['auth']);
    // Route for 2FA verification
    Route::get('/2fa', function () {
        //return view('auth.2fa'); // Your 2FA verification view
        return view('google2fa.index');
    })->name('2fa.verify')->middleware(['auth']);

    Auth::routes();
    
    Route::post('password/email', [UserController::class, 'resetAccount'])->name('password.email');
    
    //Auth::routes();

    Route::prefix('api')->middleware(['auth'])->group(function(){

    //    Route::get('/home', [HomeController::class, 'index'])->name('home');

        Route::get('/firm-accounts/all-accounts', [FirmAccountController::class, 'getAllFirmAccounts']);
        // User Management Routes
        Route::resource('users', UserController::class);
        Route::post('/users/reset-password', [UserController::class, 'resetAccount'])->middleware(['role:admin']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivateAccount'])->middleware(['role:admin']);
        Route::post('/users/{id}/activate', [UserController::class, 'activateAccount'])->middleware(['role:admin']);
        Route::post('/import-users', [UserController::class, 'importUsers']);
        Route::get('/requisitions/{requisition}/notifications', [NotificationController::class, 'index']);
        Route::post('/requisitions/{requisition}/notifications', [NotificationController::class, 'store']);
        Route::get('/requisitions/{requisition}/history', [RequisitionController::class, 'getRequisitionHistory']);
        Route::put('/requisitions/{requisition}/update', [RequisitionController::class, 'updateRequisition'])->name('requisitions.update.requisition');
        Route::post('/requisitions/search', [RequisitionController::class, 'searchRequisition'])->name('requisitions.search');
        Route::get('/requisitions/fetch-auto-fill-data', [RequisitionController::class, 'fetchAutoFillRequisition'])->name('requisitions.fetch.auto.fill');
        Route::get('/recipients', [UserController::class, 'getRecipients']);
        Route::get('/deactivated-users', [UserController::class, 'deactivatedUsers']);
        Route::resource('firm-accounts', FirmAccountController::class);
        Route::resource('audit-trails', AuditTrailController::class);
        Route::resource('organisations', OrganisationController::class);

        Route::get('organisation/all-organisations', [OrganisationController::class, 'getAllOrganisations']);
        
        Route::get('/beneficiary-accounts/search', [BeneficiaryAccountController::class, 'search']);
        Route::resource('/beneficiary-accounts', BeneficiaryAccountController::class);

        Route::get('/onceoff-accounts/{beneficiaryAccount}', [BeneficiaryAccountController::class, 'show']);
        Route::get('/onceoff-accounts', [BeneficiaryAccountController::class, 'getOnceOffAccounts']);
        Route::get('/beneficiary-accounts/{beneficiaryId?}/{accountNumber?}', [BeneficiaryAccountController::class, 'showBeneficiaryAndFirm']);
        
        Route::post('/beneficiary-accounts/{sourceAccountId}/authorize', [BeneficiaryAccountController::class, 'authorise']);

        Route::get('/accounts', [FirmAccountController::class, 'getAccounts']);
        Route::post('/firm-accounts/{sourceAccountId}/generate-file', [FirmAccountController::class, 'generateFile']);
        Route::get('/firm-accounts/{sourceAccountId}/files-details', [FirmAccountController::class, 'filesDetails']);
        Route::get('/firm-accounts/{id}/pending-confirmation-files', [FirmAccountController::class, 'getIndividualAccountPendingConfirmationFiles']);
        Route::get('/firm-accounts/{id}/recently-closed-files', [FirmAccountController::class, 'getIndividualAccountRecentlyClosedFiles']);
        Route::post('/firm-accounts/{sourceAccountId}/authorize', [FirmAccountController::class, 'authorise']);

        Route::post('/import-beneficiary-accounts', [BeneficiaryAccountController::class, 'importBeneficiaryAccounts']);
        Route::post('/import-firm-accounts', [FirmAccountController::class, 'importFirmAccounts']);
        

        Route::get('/pending-confirmation-files', [FirmAccountController::class, 'getPendingConfirmationFiles']);
        Route::get('/recently-closed-files', [FirmAccountController::class, 'getRecentlyClosedFiles']);

        Route::get('/file-management/{id}', [FileUploadController::class, 'getFileDetails']);
        Route::get('/files/{id}/requisitions', [FileUploadController::class, 'getAllRequisitionsForAFile']);
        
        Route::resource('/institutions', InstitutionController::class);

        // Custom requisition route for incomplete requisitions
        Route::get('/requisitions/incomplete', [RequisitionController::class, 'getIncompleteRequisitions'])->name('api.requisitions.incomplete');
        Route::get('/requisitions/awaiting-authorization', [RequisitionController::class, 'getAwaitingAuthorization'])->name('api.requisitions.awaiting-authorization');
        Route::get('/requisitions/awaiting-funding', [RequisitionController::class, 'countAwaitingFunding'])->name('api.requisitions.awaiting-funding');
        
        Route::get('/requisitions/ready-for-payment', [RequisitionController::class, 'getReadyForPayment'])->name('api.requisitions.ready-for-payment');
        Route::get('/requisitions/pending-payment-confirmation', [RequisitionController::class, 'getPendingPaymentConfirmation'])->name('api.requisitions.pending-payment-confirmation');
        Route::get('/requisitions/settled-today', [RequisitionController::class, 'getSettledTodayRequisitions']);
        Route::get('/requisitions/bystatus', [RequisitionController::class, 'getRequisitionsByStatus'])->name('api.requisitions.byStatus');
        Route::put('/requisitions/{requisition?}/approve', [RequisitionController::class, 'approve']);

        Route::put('/requisitions/{requisition?}/unlock', [RequisitionController::class, 'unlockRequisition']);
        Route::put('/requisitions/{requisition?}/lock', [RequisitionController::class, 'lockRequisition']);

        Route::post('/requisitions/update-status', [RequisitionController::class, 'updateStatus']);

        
        Route::post('/generate-excel-report', [ReportController::class, 'generateExcelReport']);
        Route::post('/reports/paid-by-date', [ReportController::class, 'generatePaidByDateReport']);
        
        Route::get('/report-preview', [ReportController::class, 'previewPaymentReport']);


        Route::post('/deposits/fund-deposits', [DepositController::class, 'fundDeposits']);
        Route::post('/deposits/balance-payment-fund', [DepositController::class, 'balancePaymentFund'])->name('api.deposits.balance-payment-fund');
        Route::post('/deposits/balance-payment-dont-fund', [DepositController::class, 'balancePaymentDontFund'])->name('api.deposits.balance-payment-dont-fund');

        Route::prefix('send-email')->group(function(){
            Route::post('/requestor-notification', [EmailController::class, 'sendRequestorNotificationEmail']);
            Route::post('/signatory-notification', [EmailController::class, 'sendSignatoryNotificationEmail']);
        });

        Route::post('/payments/mark-processed', [PaymentController::class, 'markProcessed']);
        Route::post('/payments/mark-failed', [PaymentController::class, 'markFailed']);
        Route::post('/payments/mark-generated', [PaymentController::class, 'markGenerated']);

        
        // Routes for document management
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
        //Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::resource('requisitions', RequisitionController::class);

        Route::resource('statuses', StatusController::class);
        //Route::resource('matters', MatterController::class);
        Route::resource('documents', DocumentController::class);
        Route::resource('payments', PaymentController::class);
        Route::resource('deposits', DepositController::class);
        

        Route::get('/category/beneficiaries/{category_id}', [CategoryController::class, 'getBeneficiariesByCategory']);
        Route::resource('categories', CategoryController::class);
        Route::resource('accounttypes', AccountTypeController::class);
        Route::get('/requisitions/{id}/documents', [DocumentController::class, 'getDocuments']);

        Route::post('/avs/verify', [AvsController::class, 'verify']);
        Route::get('/avs/status/{accountNumber}', [AvsController::class, 'getAvsStatus']);


        Route::post('/register-certificate/{user}', [CertificateController::class, 'register']);

        Route::post('/generate-certificate/{userId}', [CertificateController::class, 'generateClientCertificate'])->name('certificates.generate');
        Route::post('/delete-certificate/{userId}', [CertificateController::class, 'deleteClientCertificate'])->name('certificates.delete');
        
        Route::get('/certificates/{id}/download', [CertificateController::class, 'downloadCertificate'])->name('certificates.download');

    });


    Route::get('/secure-download/{fileId}', [RequisitionController::class, 'secureDownload'])->middleware('auth')->name('secure.download');

    Route::controller(LoginController::class)->group(function () {
        // Protect login routes with RedirectIfAuthenticated middleware
        Route::middleware(['guest', RedirectIfAuthenticated::class])->group(function () {
            Route::get('/login', 'login')->name('login'); // Login page
            Route::post('/login', 'authenticate');        // Login submission
        });

        // Logout route (accessible only to authenticated users)
        Route::middleware('auth')->get('/logout', 'logout')->name('logout');
    });
    /* Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate');
        Route::get('/logout', 'logout')->name('logout');
    });
    */
    
    Route::get('/users', [UserController::class, 'index']);
    
    Route::get('/firm-accounts', [FirmAccountController::class, 'index']);
    Route::get('/audit-trails', [AuditTrailController::class, 'index']);

    Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware(['auth','2fa']);




    // Catch-all Route for Vue Router
    Route::get('/{any}', function () {
        return view('home'); // Make sure this is the view where your Vue app is mounted
    })->where('any', '.*')->middleware(['auth','2fa']);

});//no cache route

