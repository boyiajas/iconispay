<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get all the beneficiaries for the category.
     */
    public function beneficiaries()
    {
        return $this->hasMany(BeneficiaryAccount::class, 'category_id');
    }

    public function firmAccounts()
    {
        return $this->hasMany(FirmAccount::class, 'category_id');
    }
}
