<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FileHistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_upload_id',
        'user_id',
        'action',
        'details',
        'log_date',
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fileUpload()
    {
        return $this->belongsTo(FileUpload::class);
    }

    /**
     * Static method to log file history.
     *
     * @param int $fileUploadId
     * @param string $action
     * @param string $details
     * @return void
     */
    public static function logFileHistory(int $fileUploadId, string $action, string $details): void
    {
        self::create([
            'file_upload_id' => $fileUploadId,
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'log_date' => now(),
            'organisation_id' => Auth::user()->organisation->id,
        ]);
    }
}
