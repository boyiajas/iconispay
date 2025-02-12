<?php 

namespace App\Traits;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

trait CaptureIpTrait
{
    public function getClientIp()
    {
        return request()->ip();
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
