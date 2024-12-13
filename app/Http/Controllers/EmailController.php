<?php

namespace App\Http\Controllers;

use App\Mail\RequestorNotificationMail;
use App\Mail\SignatoryNotificationMail;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    
    
    public function sendRequestorNotificationEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'greeting' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $recipient = User::find($request->recipient);
        $baseUrl = config('app.url'); // Use environment variable for base URL
        $url = "{$baseUrl}/matters/requisitions/{$recipient->id}/details";
        
        $emailData = [
            'subject' => $request->subject,
            'greeting' => $request->greeting ?? 'Dear Signatory,',
            'message' => $request->message ?? '',
            'url' => (string) $url,
            'senderName' => (string) auth()->user()->name,
            'unsubscribeLink' => (string) "{$baseUrl}/unsubscribe/{$recipient->id}",
            'preferencesLink' => (string) "{$baseUrl}/preferences/{$recipient->id}",
        ];
        //dd($emailData['subject']);
        // Send email using the Mailable
        Mail::to($recipient->email)->send(new RequestorNotificationMail($emailData));

        return response()->json(['message' => 'Email sent successfully!']);
    }

    public function sendSignatoryNotificationEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'greeting' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);
        
        $recipient = User::find($request->recipient);
        $baseUrl = config('app.url'); // Use environment variable for base URL
        $url = "{$baseUrl}/matters/requisitions/{$recipient->id}/details";
        
        $emailData = [
            'subject' => $request->subject,
            'greeting' => $request->greeting ?? 'Dear Signatory,',
            'message' => $request->message ?? '',
            'url' => (string) $url,
            'senderName' => (string) auth()->user()->name,
            'unsubscribeLink' => (string) "{$baseUrl}/unsubscribe/{$recipient->id}",
            'preferencesLink' => (string) "{$baseUrl}/preferences/{$recipient->id}",
        ];
        //dd($emailData['subject']);
        // Send email using the Mailable
        Mail::to($recipient->email)->send(new SignatoryNotificationMail($emailData));

        return response()->json(['message' => 'Email sent successfully!']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Email $email)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Email $email)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Email $email)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Email $email)
    {
        //
    }
}
