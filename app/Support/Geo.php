<?php

namespace App\Support;

/** Perhitungan jarak antar koordinat GPS. */
class Geo
{
    private const JARI_JARI_BUMI_METER = 6371000;

    /** Jarak antara 2 titik koordinat (meter), rumus Haversine. */
    public static function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::JARI_JARI_BUMI_METER * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
