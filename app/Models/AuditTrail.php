<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = ['user_name', 'action', 'details'];

    /**
     * Relationship with the User model.
     * This can be updated if you want to link audit trails to user IDs instead of user names.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_name', 'name'); // Assuming `name` is unique in the User model
    }
}
