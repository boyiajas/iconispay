<?php

use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AvsController;
use App\Http\Controllers\BeneficiaryAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FirmAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\MatterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;









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

//Auth::routes();

Route::prefix('api')->group(function(){

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::get('/recipients', [UserController::class, 'getRecipients']);
    Route::get('/deactivated-users', [UserController::class, 'deactivatedUsers']);
    Route::resource('firm-accounts', FirmAccountController::class);
    Route::resource('audit-trails', AuditTrailController::class);
    
    Route::get('/beneficiary-accounts/search', [BeneficiaryAccountController::class, 'search']);
    Route::resource('/beneficiary-accounts', BeneficiaryAccountController::class);

    Route::get('/accounts', [FirmAccountController::class, 'getAccounts']);
    Route::get('/pending-confirmation-files', [FirmAccountController::class, 'getPendingConfirmationFiles']);
    Route::get('/recently-closed-files', [FirmAccountController::class, 'getRecentlyClosedFiles']);
    
    Route::resource('/institutions', InstitutionController::class);

    // Custom requisition route for incomplete requisitions
    Route::get('/requisitions/incomplete', [RequisitionController::class, 'getIncompleteRequisitions'])->name('api.requisitions.incomplete');
    Route::get('/requisitions/awaiting-authorization', [RequisitionController::class, 'getAwaitingAuthorization'])->name('api.requisitions.awaiting-authorization');
    
    Route::get('/requisitions/ready-for-payment', [RequisitionController::class, 'getReadyForPayment'])->name('api.requisitions.ready-for-payment');
    Route::get('/requisitions/bystatus', [RequisitionController::class, 'getRequisitionsByStatus'])->name('api.requisitions.byStatus');
    Route::put('/requisitions/{requisition?}/approve', [RequisitionController::class, 'approve']);
    Route::post('/requisitions/{requisitionId}/generate-file', [RequisitionController::class, 'generateFile']);

    Route::post('/deposits/fund-deposits', [DepositController::class, 'fundDeposits']);
    Route::post('/deposits/balance-payment-fund', [DepositController::class, 'balancePaymentFund'])->name('api.deposits.balance-payment-fund');

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




Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::get('/users', [UserController::class, 'index']);
Route::get('/firm-accounts', [FirmAccountController::class, 'index']);
Route::get('/audit-trails', [AuditTrailController::class, 'index']);


Route::get('/home', [HomeController::class, 'index'])->name('home');



// Catch-all Route for Vue Router
Route::get('/{any}', function () {
    return view('home'); // Make sure this is the view where your Vue app is mounted
})->where('any', '.*');