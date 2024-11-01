<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiaryAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_text',
        'category_id',
        'account_number',
        'account_holder_type',
        'company_name',
        'initials',
        'surname',
        'id_number',
        'registration_number',
        'account_type_id',
        'institution_id',
        'branch_code',
        'my_reference',
        'recipient_reference',
        'authorised',
        'authorized_user_id',
        'authorized_at',
        'verified',
        'avs_verified_at',
        'verification_status',
        'account_found',
        'account_open',
        'account_type_verified',
        'account_type_match',
        'branch_code_match',
        'holder_name_match',
        'holder_initials_match',
        'registration_number_match',
        'user_id'
    ];

    /**
     * Relationship with the Institution model.
     * A Beneficiary Account belongs to an Institution.
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
     * Relationship with the AccountType model.
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_user_id');
    }

    /**
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with the Payment model.
     * A Beneficiary Account can have multiple payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'beneficiary_account_id');
    }
}
