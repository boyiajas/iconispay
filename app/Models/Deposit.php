<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_account_id',
        'requisition_id',  // Add requisition_id field
        'description',
        'amount',
        'funded',
        'deposit_date',
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
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Requisition
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
