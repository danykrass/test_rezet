<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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
            'password' => 'required|string|min:6',
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
        }
    
        return response()->json(['message' => 'Failed to create account'], 500);
    }    

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters.',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorResponse = [];
    
            if ($errors->has('email')) {
                $errorResponse['email'] = $errors->first('email');
            }
    
            if ($errors->has('password')) {
                $errorResponse['password'] = $errors->first('password');
            }
    
            return response()->json(['errors' => $errorResponse], 422);
        }
        $credentials = $request->only('email', 'password');

        if (!isset($credentials['password'])) {
            return response()->json(['errors' => ['password' => 'Password field is required.']], 401);
        }
        
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $errorResponse = [];
            if (!Hash::check($credentials['password'], optional($user)->password)) {
                $errorResponse['password'] = 'Field password incorrect.';
            }
            if (!$user) {
                $errorResponse['email'] = 'Field email incorrect.';
            }
        
            return response()->json(['errors' => $errorResponse], 401);
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
