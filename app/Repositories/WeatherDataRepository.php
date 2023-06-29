<?php

namespace App\Repositories;

use App\Models\WeatherData;

class WeatherDataRepository implements WeatherDataRepositoryInterface
{
    public function saveWeatherData($userId, $data)
    {
        $weatherData = WeatherData::where('user_id', $userId)->first();

        if ($weatherData) {
            $weatherData->update($data);
        } else {
            $data['user_id'] = $userId;
            WeatherData::create($data);
        }
    }

    public function getWeatherData($userId)
    {
        $weatherData = WeatherData::where('user_id', $userId)->first();

        if ($weatherData) {

            return [
                'temp' => $weatherData->temp,
                'pressure' => $weatherData->pressure,
                'humidity' => $weatherData->humidity,
                'temp_min' => $weatherData->temp_min,
                'temp_max' => $weatherData->temp_max,
            ];
        } else {
            return null;
        }
    }
}
