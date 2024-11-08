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
        'authorised',
        'verified',
        'verification_status',
    ];

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
        return $this->belongsTo(BeneficiaryAccount::class);
    }
}
