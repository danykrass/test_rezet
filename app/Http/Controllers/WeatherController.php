<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\WeatherServiceInterface;

class WeatherController extends Controller
{
    private $weatherService;

    public function __construct(WeatherServiceInterface $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function fetchWeatherData(Request $request)
    {
        $uuid = $request->cookie('uuid');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $weatherData = $this->weatherService->getWeatherData($latitude, $longitude);

        Redis::set($uuid, json_encode($weatherData));
        Redis::expire($uuid, 3600); 

        return $weatherData;
    }
}
