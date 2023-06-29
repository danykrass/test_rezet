<?php

namespace App\Repositories;

interface WeatherDataRepositoryInterface
{
    public function saveWeatherData($userId, $data);
    public function getWeatherData($userId);
}
