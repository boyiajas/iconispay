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
        'completed_at',
        'created_by',
        'locked_at',
        'locked_by',
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
        return $this->belongsTo(FirmAccount::class);
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_user_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The requisition can have many history logs.
     */
    public function histories()
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

     /**
     * Relationship with FileUpload model.
     */
    /* public function fileUploads()
    {
        return $this->hasMany(FileUpload::class);
    } */

    public function fileUploads()
    {
        return $this->belongsToMany(FileUpload::class, 'file_upload_requisition')->withTimestamps();
    }


    /**
     * Get the count of generated files for the requisition.
     */
    public function getGeneratedFileCountAttribute()
    {
        return $this->fileUploads()->count();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the FileUpload ID the requisition belongs to.
     *
     * @return int|null
     */
    public function getFileUploadId()
    {
        $fileUpload = $this->fileUploads()->first();

        return $fileUpload ? $fileUpload->id : null;
    }
}
