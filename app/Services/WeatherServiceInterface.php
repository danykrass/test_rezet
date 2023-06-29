<?php

namespace App\Services;

interface WeatherServiceInterface
{
    public function getWeatherData($latitude, $longitude);
    public function updateWeatherData($userId, $latitude, $longitude);
    public function getCachedWeatherData($userId);
    public function cacheWeatherData($userId, $data);
}
