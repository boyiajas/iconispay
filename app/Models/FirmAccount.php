<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FirmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_text',
        'category_id',
        'account_holder_type',
        'method', // e.g., "Manual" or "File Upload"
        'account_holder',
        'account_number',
        'account_type_id',
        'institution_id',
        'branch_code',
        'branch_name',
        'swift_code',
        'initials',
        'surname',
        'company_name',
        'id_number',
        'registration_number',
        'my_reference',
        'recipient_reference',
        'verified',
        'verification_status',
        'account_found',
        'account_open',
        'account_type_verified',
        'account_type_match',
        'branch_code_match',
        'holder_name_match',
        'holder_initials_match',
        'registration_number_match',
        'authorised',
        'avs_verified_at',
        'number_of_authorizer',
        'user_id',
        'aggregated',
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

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

     /**
     * Relationship with the Authorizer model.
     */
    public function authorizers()
    {
        return $this->hasMany(Authorizer::class);
    }

    /**
     * Method to get authorizer details including the authorized date.
     */
    public function getAuthorizerDetails()
    {
        return $this->authorizers->map(function ($authorizer) {
            return [
                'user_id' => $authorizer->user_id,
                'authorized_date' => $authorizer->authorized_date,
                'user_name' => $authorizer->user->name, // Assuming the User model has a 'name' attribute
                'email' => $authorizer->user->email,
            ];
        });
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
