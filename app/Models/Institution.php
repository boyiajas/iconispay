<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'short_name', 'branch_code'];

    /**
     * Relationship with the BeneficiaryAccount model.
     * An Institution can have many Beneficiary Accounts.
     */
    public function beneficiaryAccounts()
    {
        return $this->hasMany(BeneficiaryAccount::class);
    }

    /**
     * Relationship with the FirmAccount model.
     * An Institution can have many Firm Accounts.
     */
    public function firmAccounts()
    {
        return $this->hasMany(FirmAccount::class);
    }
}
