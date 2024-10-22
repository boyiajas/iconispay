<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matter extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'status_id',
        'file_reference',
        'reason',
        'properties',
        'parties',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship to Status
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
