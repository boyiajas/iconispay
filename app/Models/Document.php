<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'description',
        'requisition_id',
        'created_by',
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
    // Relation to Requisition
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}