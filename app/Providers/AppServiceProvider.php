<?php

namespace App\Providers;

use App\Models\BeneficiaryAccount;
use App\Models\Certificate;
use App\Models\Deposit;
use App\Models\Document;
use App\Models\FileUpload;
use App\Models\FirmAccount;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\User;
use App\Observers\AuditTrailObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(AuditTrailObserver::class);
        Payment::observe(AuditTrailObserver::class);
        Deposit::observe(AuditTrailObserver::class);
        Requisition::observe(AuditTrailObserver::class);
        BeneficiaryAccount::observe(AuditTrailObserver::class);
        FirmAccount::observe(AuditTrailObserver::class);
        FileUpload::observe(AuditTrailObserver::class);
        Document::observe(AuditTrailObserver::class);
        Certificate::observe(AuditTrailObserver::class);
    }
}
