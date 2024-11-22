<?php

use App\Exports\SimpleExport;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\AvsController;
use App\Http\Controllers\BeneficiaryAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FirmAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\MatterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;




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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/complete-registration', [RegisterController::class, 'completeRegistration'])->name('complete.registration');
Route::get('/complete-2fa-setup', [RegisterController::class, 'complete2FASetup'])->name('complete.2fa.setup');
Route::post('/verify-2fa', [TwoFactorAuthController::class, 'verify2fa'])->name('verify.2fa');
Route::get('/setup-2fa', [RegisterController::class, 'redirectTo2FASetup'])->name('setup.2fa')->middleware('auth');
// Route for 2FA verification
Route::get('/2fa', function () {
    //return view('auth.2fa'); // Your 2FA verification view
    return view('google2fa.index');
})->name('2fa.verify')->middleware('auth');


Auth::routes();

Route::prefix('api')->middleware(['auth'])->group(function(){

//    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/firm-accounts/all-accounts', [FirmAccountController::class, 'getAllFirmAccounts']);
    // User Management Routes
    Route::resource('users', UserController::class);
    Route::get('/recipients', [UserController::class, 'getRecipients']);
    Route::get('/deactivated-users', [UserController::class, 'deactivatedUsers']);
    Route::resource('firm-accounts', FirmAccountController::class);
    Route::resource('audit-trails', AuditTrailController::class);
    
    Route::get('/beneficiary-accounts/search', [BeneficiaryAccountController::class, 'search']);
    Route::resource('/beneficiary-accounts', BeneficiaryAccountController::class);
    Route::get('/beneficiary-accounts/{beneficiaryId?}/{accountNumber?}', [BeneficiaryAccountController::class, 'showBeneficiaryAndFirm']);
    
    Route::post('/beneficiary-accounts/{sourceAccountId}/authorize', [BeneficiaryAccountController::class, 'authorise']);

    Route::get('/accounts', [FirmAccountController::class, 'getAccounts']);
    Route::post('/firm-accounts/{sourceAccountId}/generate-file', [FirmAccountController::class, 'generateFile']);
    Route::get('/firm-accounts/{sourceAccountId}/files-details', [FirmAccountController::class, 'filesDetails']);
    Route::get('/firm-accounts/{id}/pending-confirmation-files', [FirmAccountController::class, 'getIndividualAccountPendingConfirmationFiles']);
    Route::get('/firm-accounts/{id}/recently-closed-files', [FirmAccountController::class, 'getIndividualAccountRecentlyClosedFiles']);
    Route::post('/firm-accounts/{sourceAccountId}/authorize', [FirmAccountController::class, 'authorise']);
    

    Route::get('/pending-confirmation-files', [FirmAccountController::class, 'getPendingConfirmationFiles']);
    Route::get('/recently-closed-files', [FirmAccountController::class, 'getRecentlyClosedFiles']);

    Route::get('/file-management/{id}', [FileUploadController::class, 'getFileDetails']);
    
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
    Route::resource('matters', MatterController::class);
    Route::resource('documents', DocumentController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('deposits', DepositController::class);
    

    Route::get('/category/beneficiaries/{category_id}', [CategoryController::class, 'getBeneficiariesByCategory']);
    Route::resource('categories', CategoryController::class);
    Route::resource('accounttypes', AccountTypeController::class);
    Route::get('/requisitions/{id}/documents', [DocumentController::class, 'getDocuments']);

    Route::post('/avs/verify', [AvsController::class, 'verify']);

});


Route::get('/secure-download/{fileId}', [RequisitionController::class, 'secureDownload'])->middleware('auth')->name('secure.download');

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::get('/users', [UserController::class, 'index']);
Route::get('/firm-accounts', [FirmAccountController::class, 'index']);
Route::get('/audit-trails', [AuditTrailController::class, 'index']);

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware(['auth','2fa']);




// Catch-all Route for Vue Router
Route::get('/{any}', function () {
    return view('home'); // Make sure this is the view where your Vue app is mounted
})->where('any', '.*')->middleware(['auth']);