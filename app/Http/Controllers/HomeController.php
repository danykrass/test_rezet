<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeatherData;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $uuid = request()->cookie('uuid');
        $weather = Redis::get($uuid);
        $weatherData = json_decode($weather);

        return response()->json(['user' => $user, 'main' => $weatherData], 200);
    }
    
}
