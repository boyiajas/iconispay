<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EmailPreviewController extends Controller
{
    /**
     * Preview Signatory Notification Email
     */
    public function signatoryNotificationPreview()
    {
        $emailData = [
            'subject' => 'Matter ready for authorisation (LP0018TR)',
            'greeting' => 'Dear Peter',
            'message' => 'A matter with file reference: LP0018TR is ready for authorisation.',
            'url' => 'http://localhost/matters/requisitions/3/details',
            'senderName' => 'Peter Ajakaiye',
            'unsubscribeLink' => 'http://localhost/unsubscribe/3',
            'preferencesLink' => 'http://localhost/preferences/3',
        ];

        // Render the email view with dummy data
        return new App\Mail\SignatoryNotificationMail($emailData);
    }

    /**
     * Get dummy data for email preview.
     */
    private function getDummyData(): array
    {
        return [
            'subject' => 'Matter ready for authorisation (LP0018TR)',
            'greeting' => 'Dear Peter',
            'message' => 'A matter with file reference: LP0018TR is ready for authorisation.',
            'url' => 'http://localhost/matters/requisitions/3/details',
            'senderName' => 'Peter Ajakaiye',
            'unsubscribeLink' => 'http://localhost/unsubscribe/3',
            'preferencesLink' => 'http://localhost/preferences/3',
        ];
    }
}
