<?php

namespace App\Http\Controllers;

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
            'recipient' => 'required|exists:recipients,id',
            'subject' => 'required|string|max:255',
            'greeting' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $recipient = User::find($request->recipient);
        $emailData = [
            'subject' => $request->subject,
            'greeting' => $request->greeting,
            'message' => $request->message,
            'url' => 'https://app.lexispay.co.za/matters/305633/requisition/payments/to/review',
            'senderName' => auth()->user()->name,
        ];

        Mail::send('emails.notification', $emailData, function ($message) use ($recipient, $request) {
            $message->to($recipient->email)
                    ->subject($request->subject);
        });

        return response()->json(['message' => 'Email sent successfully!']);
    }

    public function sendSignatoryNotificationEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required|exists:recipients,id',
            'subject' => 'required|string|max:255',
            'greeting' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $recipient = User::find($request->recipient);
        $emailData = [
            'subject' => $request->subject,
            'greeting' => $request->greeting,
            'message' => $request->message,
            'url' => 'https://app.lexispay.co.za/matters/305633/requisition/payments/to/review',
            'senderName' => auth()->user()->name,
        ];

        Mail::send('emails.notification', $emailData, function ($message) use ($recipient, $request) {
            $message->to($recipient->email)
                    ->subject($request->subject);
        });

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
