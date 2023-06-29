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
        $apiKey = 'd5caae74628fc2cc9bfacc60aa6855c9';
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
    
    public function updateWeatherData($userId, $latitude, $longitude)
    {   
        $userId = Auth::id();
        $weatherData = WeatherData::where('user_id', $userId)->first();
        $uuid = request()->cookie('uuid');
        if (!$weatherData) {
            WeatherData::create([
                'user_id' => $userId,
                'uuid' => $uuid,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        } else {
            $weatherData->latitude = $latitude;
            $weatherData->longitude = $longitude;
            $weatherData->save();
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