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
        'category_id',
        'account_holder_type',
        'registration_number',
        'initials',
        'surname',
        'company_name',
        'account_number',
        'account_holder',
        'description',
        'amount',
        'my_reference',
        'recipient_reference',
        'institution_id',
        'account_type_id',
        'branch_code',
        'id_number',
        'verified',
        'user_id',
        'authorised',
        'verified',
        'verification_status',
        'account_found',
        'account_open',
        'account_type_verified',
        'account_type_match',
        'branch_code_match',
        'holder_name_match',
        'holder_initials_match',
        'registration_number_match',
    ];

    /**
     * A payment belongs to an institution
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

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
    // Relationship with Requisition
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
