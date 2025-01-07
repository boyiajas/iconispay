<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Requisition;
use App\Enums\NotificationType;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Requisition $requisition)
    {
        $notifications = $requisition->notifications->groupBy('notification_type');
        $response = [];
        foreach ($notifications as $type => $group) {
            $response[$type] = $group->map(fn($notification) => $notification->user);
        }
        return $response;
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
    public function store(Request $request, Requisition $requisition)
    {
        $validated = $request->validate([
            'matter_authorized' => 'array',
            'matter_authorized.*.id' => 'exists:users,id',
            'matter_unlocked' => 'array',
            'matter_unlocked.*.id' => 'exists:users,id',
            'payment_successful' => 'array',
            'payment_successful.*.id' => 'exists:users,id',
            'payment_failed' => 'array',
            'payment_failed.*.id' => 'exists:users,id',
        ]);

        $requisition->notifications()->delete();

         // Map keys to NotificationType enum values
      /*   $typeMapping = [
            'matter_authorized' => NotificationType::MATTER_AUTHORISED,
            'matter_unlocked' => NotificationType::MATTER_UNLOCKED,
            'payment_successful' => NotificationType::PAYMENT_PROCESSING_SUCCESSFUL,
            'payment_failed' => NotificationType::PAYMENT_PROCESSING_FAILED,
        ]; */

        foreach ($request->all() as $type => $userIds) {
            if (!NotificationType::tryFrom($type)) {
                return response()->json(['error' => "Invalid notification type: $type"], 422);
            }

            foreach ($userIds as $userId) {
                $requisition->notifications()->create([
                    'notification_type' => $type,
                    'user_id' => $userId,
                ]);
            }
        }

       /*  dd($validated);
         // Loop through validated data and create notifications
        foreach ($validated as $type => $users) {
            if (isset($typeMapping[$type])) {
                foreach ($users as $user) {
                    $requisition->notifications()->create([
                        'notification_type' => $typeMapping[$type]->value,
                        'user_id' => $user['id'],
                    ]);
                }
            }
        } */

        return response()->json(['message' => 'Notifications updated successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }
}
