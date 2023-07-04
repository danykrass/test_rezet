<?php

namespace App\Services;

use App\Models\WeatherData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class WeatherService implements WeatherServiceInterface
{
    public function getWeatherData($latitude, $longitude)
    {
        $apiKey = '';
        $apiUrl = 'https://api.openweathermap.org/data/2.5/weather?lat=' . $latitude . '&lon=' . $longitude . '&appid=' . $apiKey;

        try {
            $response = Http::get($apiUrl);
            $data = $response->json();

            return [
                'temp' => $data['main']['temp'],
                'pressure' => $data['main']['pressure'],
                'humidity' => $data['main']['humidity'],
                'temp_min' => $data['main']['temp_min'],
                'temp_max' => $data['main']['temp_max'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCachedWeatherData($userId)
    {
        $cacheKey = 'weather:' . $userId;
        $cachedData = Redis::get($cacheKey);

        return $cachedData ? json_decode($cachedData, true) : null;
    }

    public function cacheWeatherData($userId, $data)
    {
        $cacheKey = 'weather:' . $userId;
        $cacheData = json_encode($data);

        Redis::set($cacheKey, $cacheData);
    }
}
