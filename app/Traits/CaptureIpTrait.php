<?php 

namespace App\Traits;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

trait CaptureIpTrait
{
    public function getClientIp()
    {
        $request = request();

        // ✅ Trust the correct headers for real IP detection
        $ipAddress = $request->header('X-Forwarded-For') 
            ?? $request->header('CF-Connecting-IP') 
            ?? $request->header('X-Real-IP') 
            ?? $request->server('REMOTE_ADDR');

        // If multiple IPs are detected in X-Forwarded-For, get the first one (real IP)
        if (strpos($ipAddress, ',') !== false) {
            $ipAddress = explode(',', $ipAddress)[0];
        }

        return trim($ipAddress);
    }

    public function getUserAgent()
    {
        return request()->header('User-Agent');
    }

    public function getGeoLocation()
    {
        $ip = $this->getClientIp();

        try {
            $location = Location::get($ip);
            return $location ? [
                'country' => $location->countryName,
                'city' => $location->cityName,
                'region' => $location->regionName,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'timezone' => $location->timezone,
            ] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
