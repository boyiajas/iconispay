<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'display',
        'category_id',
        'account_holder_type',
        'account_holder',
        'account_number',
        'account_type_id',
        'institution_id',
        'branch_code',
        'aggregated',
        'authorised',
        'mandated',
    ];

    /**
     * Relationship with the Institution model.
     * A Firm Account belongs to an Institution.
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

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
}
