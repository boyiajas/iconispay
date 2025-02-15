<?php

use App\Models\RequisitionHistory;
use Illuminate\Support\Facades\Auth;

if (!function_exists('logHistory')) {
    /**
     * Logs history for a requisition.
     *
     * @param int $requisitionId
     * @param string $action
     * @param string|null $details
     * @return void
     */
    function logHistory($requisitionId, $action, $details = null)
    {
        RequisitionHistory::create([
            'requisition_id' => $requisitionId,
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
            'organisation_id' => Auth::user()->organisation->id,
        ]);
    }
}
