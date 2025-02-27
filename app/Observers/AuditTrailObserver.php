<?php

namespace App\Observers;

use App\Jobs\AuditTrailJob;
use Illuminate\Support\Facades\Auth;

class AuditTrailObserver
{

    public function created($model)
    {
        $this->logAction('Created', $model, null, $model->toArray());
    }

    public function updated($model)
    {
        $this->logAction('Updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted($model)
    {
        $this->logAction('Deleted', $model, $model->getOriginal(), null);
    }

    public static function logCustomAction($action, $model, $oldValues = null, $newValues = null)
    {
        $observer = new self(); // Create an instance of the observer
        $observer->logAction($action, $model, $oldValues, $newValues);
    }

    private function logAction($action, $model, $oldValues = null, $newValues = null)
    {
        $user = Auth::user();
        $data = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->roles->pluck('name')->implode(', '),
            'action' => $action,
            'model_type' => is_object($model) ? get_class($model) : 'CustomAction',
            'model_id' => is_object($model) ? $model->id ?? null : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_agent' => request()->header('User-Agent'), // ✅ Only get user agent here
            'organisation_id' => $user?->organisation?->id,
        ];

        // ✅ Dispatch Job Asynchronously (IP & Geo-location will be handled in the job)
        AuditTrailJob::dispatch($data);
    }
}
