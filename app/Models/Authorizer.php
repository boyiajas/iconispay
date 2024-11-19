<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authorizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_account_id',
        'beneficiary_account_id',
        'user_id',
    ];

    /**
     * Relationship with the FirmAccount model.
     */
    public function firmAccount()
    {
        return $this->belongsTo(FirmAccount::class);
    }

    /**
     * Relationship with the BeneficiaryAccount model.
     */
    public function beneficiaryAccount()
    {
        return $this->belongsTo(BeneficiaryAccount::class);
    }

    /**
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
