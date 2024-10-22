<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Relationship to Matters
    public function matters()
    {
        return $this->hasMany(Matter::class, 'status_id');
    }
}
