<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'file_name',
        'file_path',
        'file_size',
        'generated_at',
        'user_id',
    ];

    /**
     * Relationship with Requisition model.
     * A file upload belongs to a requisition.
     */
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    /**
     * Relationship with User model.
     * A file upload is created by a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
