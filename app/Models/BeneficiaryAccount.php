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
        'registration_number',
        'account_type_id',
        'institution_id',
        'branch_code',
        'my_reference',
        'recipient_reference',
        'authorised',
    ];

    /**
     * Relationship with the Institution model.
     * A Beneficiary Account belongs to an Institution.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function accounttype()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }
}
