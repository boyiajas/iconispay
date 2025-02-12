<?php

namespace App\Observers;

use App\Jobs\AuditTrailJob;
use App\Traits\CaptureIpTrait;
use Illuminate\Support\Facades\Auth;

class AuditTrailObserver
{
    use CaptureIpTrait;

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
        $geoLocation = $this->getGeoLocation();

        $data = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->roles->pluck('name')->implode(', '),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
            'country' => $geoLocation['country'] ?? null,
            'city' => $geoLocation['city'] ?? null,
            'region' => $geoLocation['region'] ?? null,
            'latitude' => $geoLocation['latitude'] ?? null,
            'longitude' => $geoLocation['longitude'] ?? null,
            'timezone' => $geoLocation['timezone'] ?? null,
        ];

        // ✅ Dispatch Job Asynchronously
        AuditTrailJob::dispatch($data);
    }
}
