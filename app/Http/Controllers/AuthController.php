<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeatherData;
use App\Services\AuthService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $this->authService->registerUser($request->all());
        $latitude = $request->input('lat');
        $longitude = $request->input('long');

        if ($latitude && $longitude) {
            $this->authService->updateWeatherData($user->id, $latitude, $longitude);
            Redis::set('user:' . $user->id . ':coordinates', json_encode([
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]));
        }
        if ($user) {
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json(['token' => $token], 201);
        } else {
            return response()->json(['message' => 'Failed to create account'], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        try {
            if (!Auth::attempt($data)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;

            $latitude = $request->input('lat');
            $longitude = $request->input('long');

            if ($latitude && $longitude) {
                $this->authService->updateWeatherData($user->id, $latitude, $longitude);
                Redis::set('user:' . $user->id . ':coordinates', json_encode([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]));
            }

            return response()->json(['token' => $token], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 401);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();
        $loggedInUser = $this->authService->loginWithGoogle($user->token);
        $latitude = request()->input('lat');
        $longitude = request()->input('long');
        
        if ($loggedInUser && $latitude && $longitude) {
            $this->authService->updateWeatherData($loggedInUser->id, $latitude, $longitude);
            Redis::set('user:' . $loggedInUser->id . ':coordinates', json_encode([
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]));
        }
        

        return redirect('/');
    }

    public function logout(Request $request)
    {
        $userId = Auth::user()->id;
    
        Redis::del('user:' . $userId . ':coordinates');
    
        Auth::user()->tokens()->delete();
    
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/');
    }
    
}
