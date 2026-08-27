<?php

namespace App\Services;

use App\Enums\DeviceType;

class DeviceDetector
{
    /**
     * Determine the device type from a user agent string.
     *
     * Order matters: tablets must be checked before generic mobile patterns
     * (e.g. "Android" appears in both iPad-on-iOS and phone user agents).
     */
    public function detect(string $userAgent): DeviceType
    {
        $ua = strtolower($userAgent);

        if ($this->isTablet($ua)) {
            return DeviceType::Tablet;
        }

        if ($this->isMobile($ua)) {
            return DeviceType::Mobile;
        }

        return DeviceType::Desktop;
    }

    /**
     * Tablet indicators: iPad (including modern iPadOS which reports Macintosh),
     * Android tablets, Kindles, and generic tablet tokens.
     */
    protected function isTablet(string $ua): bool
    {
        if (preg_match('/ipad|tablet|playbook|silk|kindle|tizen/', $ua)) {
            return true;
        }

        // Modern iPads identify as "Macintosh" but expose a touch interface.
        // iPhones also report "Mac OS X" + "Mobile", so exclude phone tokens.
        if (preg_match('/macintosh|mac os x/', $ua) && preg_match('/mobile/', $ua) && ! preg_match('/iphone|ipod/', $ua)) {
            return true;
        }

        // Android that is explicitly not a phone.
        return preg_match('/android(?!.*mobile)/', $ua);
    }

    /**
     * Phone indicators: mobile tokens common to smartphones.
     */
    protected function isMobile(string $ua): bool
    {
        return (bool) preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|opera mobi|iemobile|windows phone|phone/', $ua);
    }
}
