<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_account_id',
        'requisition_ids',
        'file_name',
        'file_path',
        'file_size',
        'file_hash',
        'generated_at',
        'user_id',
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    protected $casts = [
        'generated_at' => 'datetime', // Cast to datetime
        'requisition_ids' => 'array',
    ];

    /**
     * Relationship with Requisition model.
     * A file upload belongs to a requisition.
     */
    public function requisitions()
    {
        return $this->belongsToMany(Requisition::class, 'file_upload_requisition')->withTimestamps();
    }

    /**
     * Relationship with User model.
     * A file upload is created by a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     /**
     * Relationship with the FirmAccount model.
     * A file upload belongs to a firm account.
     */
    public function firmAccount()
    {
        return $this->belongsTo(FirmAccount::class);
    }

    /**
     * Override the delete method to remove the file from the local storage.
     */
    public function delete()
    {
        // Delete the file from the local storage if it exists
        if (Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }

        // Call the parent delete method to remove the record from the database
        parent::delete();
    }
}
