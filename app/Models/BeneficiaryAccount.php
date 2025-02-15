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
        'branch_name',
        'swift_code',
        'my_reference',
        'recipient_reference',
        'authorised',
        'verified',
        'avs_verified_at',
        'number_of_authorizer',
        'verification_status',
        'account_found',
        'account_open',
        'account_open_gt_three_months',
        'account_type_verified',
        'account_type_match',
        'branch_code_match',
        'holder_name_match',
        'holder_initials_match',
        'registration_number_match',
        'user_id',
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
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

     /**
     * Relationship with the Authorizer model.
     */
    public function authorizers()
    {
        return $this->hasMany(Authorizer::class);
    }

    /**
     * Method to get authorizer details including the authorized date.
     */
    public function getAuthorizerDetails()
    {
        return $this->authorizers->map(function ($authorizer) {
            return [
                'user_id' => $authorizer->user_id,
                'authorized_date' => $authorizer->created_at,
                'user_name' => $authorizer->user->name, // Assuming the User model has a 'name' attribute
                'email' => $authorizer->user->email
            ];
        });
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
        return $this->hasMany(Payment::class, 'beneficiary_account_id')->whereStatus('processed');
    }
}
