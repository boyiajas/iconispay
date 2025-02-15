<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'firm_account_id',
        'requisition_id',
        'beneficiary_account_id',
        'category_id',
        'description',
        'amount',
        'my_reference',
        'recipient_reference',
        'user_id',
        'status',
        'mark_processed_at',
        'authorised',
        'verified',
        'verification_status',
        'account_type',
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * A payment belongs to an institution
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Relationship with the Category model.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A payment has an account type
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    } 

    /**
     * Relationship with the FirmAccount model.
     */
    public function firmAccount()
    {
        return $this->belongsTo(FirmAccount::class);
    }

     /**
     * Relationship with the source FirmAccount (account to pay from).
     */
    public function sourceFirmAccount()
    {
        return $this->belongsTo(FirmAccount::class, 'firm_account_id');
    }
    /**
     * Relationship with the FirmAccount model for the "pay to" account.
     */
    public function payToFirmAccount()
    {
        return $this->belongsTo(FirmAccount::class, 'beneficiary_account_id');
    }
    /**
     * Relationship with the Requisition model.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Relationship with the BeneficiaryAccount model.
     */
    public function beneficiaryAccount()
    {
        return $this->belongsTo(BeneficiaryAccount::class, 'beneficiary_account_id');
    }

     /**
     * Custom method to eager load the correct "pay to" account based on account_type.
     */
    public function loadPayToAccount()
    {
        if ($this->account_type === 'B') {
            return $this->beneficiaryAccount()->getResults();
        } elseif ($this->account_type === 'F') {
            return $this->payToFirmAccount()->getResults();
        }

        return null;
    }

    /**
     * Custom accessor to get the correct "pay to" account based on account_type.
     */
    public function getPayToAccountAttribute()
    {
        return $this->loadPayToAccount();
    }

    public static function getBeneficiaryByIdAndAccountType($id, $accountType)
    {
       
        return BeneficiaryAccount::whereId($id)->whereAccountType($accountType);
    }
}
