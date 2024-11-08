<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FirmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'display',
        'category_id',
        'account_holder_type',
        'method', // e.g., "Manual" or "File Upload"
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    public function files()
    {
        return $this->hasMany(FileUpload::class, 'firm_account_id');
    }

    /**
     * Check if the account has pending requisitions for confirmation.
     */
    public function getPendingConfirmationFilesAttribute()
    {
        // Eager load FileUploads with their associated requisitions
        $fileUploads = FileUpload::with(['requisitions' => function ($query) {
            $query->where('status_id', 5); // Only load requisitions with status_id of 5
        }])->where('firm_account_id', $this->id)->get();

        $fileLinks = [];

        foreach ($fileUploads as $fileUpload) {
            // Check if the fileUpload has any pending requisitions
            if ($fileUpload->requisitions->isNotEmpty()) {
                $relativePath = str_replace('public/', '', $fileUpload->file_path);
                $fileLinks[] = [
                    'file_id' => $fileUpload->id,
                    'file_name' => $fileUpload->file_name,
                    'download_url' => Storage::url($relativePath),
                    'generated_at' => $fileUpload->generated_at,
                ];
            }
        }

        return $fileLinks;
    }
    




    /**
     * Format the display for Ready for Payment.
     */
    public function getReadyForPaymentAttribute()
    {
        return "{$this->requisitions()->where('status_id', 5)->count()} Matter(s)";
    }
}
