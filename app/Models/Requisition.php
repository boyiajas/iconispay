<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_reference',
        'reason',
        'parties',
        'properties',
        'matter_id',
        'status_id',
        'firm_account_id',
        'transaction_value',
        'capturing_status',
        'authorization_status',
        'authorized_user_id',
        'authorized_at',
        'locked',
        'funding_status',
        'settlement_status',
        'created_by',
    ];

    /**
     * The requisition belongs to a matter.
     */
    public function matter()
    {
        return $this->belongsTo(Matter::class);
    }

    public function firmAccount()
    {
        return $this->belongTo(FirmAccount::class, 'firm_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The requisition can have many history logs.
     */
    public function historyLogs()
    {
        return $this->hasMany(RequisitionHistory::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

     /**
     * The requisition can have many deposits.
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function calculateTransactionValue()
    {
        $totalPayments = $this->payments()->sum('amount');
        $this->transaction_value = $totalPayments;
        $this->save();

        return $this->transaction_value;
    }
}
