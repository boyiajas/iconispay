<?php

namespace App\Policies;

use App\Models\FileUpload;
use App\Models\User;

class FileUploadPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function download(User $user, FileUpload $fileUpload)
    {
        // Define your logic, for example:
        // Only allow if the file belongs to the user's requisition or a similar check
        return true; // or your specific condition
    }
}
