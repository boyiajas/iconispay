<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'contact',
        'email',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function fileHistoryLogs()
    {
        return $this->hasMany(FileHistoryLog::class);
    }

    public function firmAccounts()
    {
        return $this->hasMany(FirmAccount::class);
    }

    public function beneficiaryAccounts()
    {
        return $this->hasMany(BeneficiaryAccount::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    public function requisitionHistories()
    {
        return $this->hasMany(RequisitionHistory::class);
    }

    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function authorizers()
    {
        return $this->hasMany(Authorizer::class);
    }
}
