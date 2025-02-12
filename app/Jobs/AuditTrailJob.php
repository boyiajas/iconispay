<?php

namespace App\Jobs;

use App\Models\AuditTrail;
use App\Traits\CaptureIpTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuditTrailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CaptureIpTrait;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // ✅ Fetch IP Address inside the Job
        $ipAddress = $this->getClientIp();

        // ✅ Fetch Geo-Location inside the Job (to prevent slowdowns)
        $geoLocation = $this->getGeoLocation();

        // ✅ Merge IP Address & Geo-Location Data into the Log
        $this->data = array_merge($this->data, [
            'ip_address' => $ipAddress,
            'country' => $geoLocation['country'] ?? null,
            'city' => $geoLocation['city'] ?? null,
            'region' => $geoLocation['region'] ?? null,
            'latitude' => $geoLocation['latitude'] ?? null,
            'longitude' => $geoLocation['longitude'] ?? null,
            'timezone' => $geoLocation['timezone'] ?? null,
        ]);

        // ✅ Store in Database
        AuditTrail::create($this->data);
    }
}
