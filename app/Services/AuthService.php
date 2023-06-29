<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeatherData;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Redis;

class AuthService
{
    private $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }
    public function registerUser(array $data)
    {
        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);


            $user->status = 'Active';
            $user->save();
    
            Auth::login($user);
    
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function login(array $data)
    {
        if (isset($data['token'])) {
            return $this->loginWithGoogle($data['token']);
        } else {
            return $this->loginWithEmail($data);
        }
    }

    public function loginWithGoogle($token)
    {
        $googleUser = Socialite::driver('google')->userFromToken($token);

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $randomPassword = Str::random(10);
            try {
                $user = User::create([
                    'first_name' => $googleUser->user['name'],
                    'last_name' => $googleUser->user['given_name'],
                    'profile' => $googleUser->avatar,
                    'email' => $googleUser->email,
                    'password' => Hash::make($randomPassword),
                ]);
            } catch (\Exception $e) {
                return null;
            }
        }

        $user->status = 'Active';
        $user->save();

        Auth::login($user);

        return $user;
    }
    public function loginWithEmail(array $data)
    {
        $email = $data['email'];
        $password = $data['password'];
        $userId = Auth::id();
        $uuid = $request->cookie('uuid');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            $weatherData = WeatherData::where('uuid', $user->uuid)->first();
            if ($weatherData && $weatherData->user_id) {
                $user->status = 'Active';
                $user->save();
            }

            $this->authService->getWeatherData($userId, $uuid);
            return $user;
        }

        return null;
    }

}
