<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:users',
            'fullname' => 'required|string',
            // 'email' => 'required|email|unique:users',
            // 'password' => 'required|confirmed|string|min:6',
            'role' => 'required|string',
        ]);

        // $setting = Setting::first();

        // $status = "";

        // if($setting){
        //     if($request->role === "pre"){
        //         if($setting->prereg === "active"){
        //             $status = "active";
        //         } else {
        //             $status = "inactive";
        //         }
        //     } else if($request->role === "ons"){
        //         if($setting->onsite === "active"){
        //             $status = "active";
        //         } else {
        //             $status = "inactive";
        //         }
        //     } else {
        //         $status = "active";
        //     }
        // } else {
        //     $status = "inactive";
        // }

        $user = User::create([
            'name' => $data['name'],
            // 'email' => $data['email'],
            'fullname' => $data['fullname'],
            'role' => $data['role'],
            'password' => Hash::make(123456),
            // 'status' => 'active',
        ]);

        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required',
            'required_settings' => 'required',
            'required_role' => 'required',
            'registration_method' => 'nullable' // i set it to nullalbe becuase of admin role who dont need it, make sure to add registration method to user logins
        ]);

        $user = User::where('name', $credentials['name'])->first();
        $settings = Setting::first();

        if(!$settings){
            throw ValidationException::withMessages([
                'name' => ['Settings not configured yet. Please try again later.'],
            ]);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        if($user && $user->status !== "active"){
            throw ValidationException::withMessages([
                'name' => ['Inactive Account.'],
            ]);
        }

        if($request->required_role !== $user->role){
            throw ValidationException::withMessages([
                'name' => ['Invalid Account Role.'],
            ]);
        }
        
        if($request->required_settings === "prereg" && $settings->prereg !== "active"){
            throw ValidationException::withMessages([
                'name' => ['Pre Registration is currently close.'],
            ]);
        } else if ($request->required_settings === "onsite" && $settings->onsite !== "active"){
            throw ValidationException::withMessages([
                'name' => ['Onsite Registration is currently close.'],
            ]);
        }
        // if ($user->role !== 'admin' && (($user && $user->status !== "active") || ($request->required_settings === "prereg" && $settings->pregreg !== 'active') || ($request->required_settings === "onsite" && $settings->onsite !== 'active'))) {
        //     throw ValidationException::withMessages([
        //         'name' => ['Invalid Account.'],
        //         'required_settings' => $request->required_settings,
        //         'settings' => $settings,
        //     ]);
        // }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'registration_method' => $request->registration_method,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $loginUrl = "";

        if($user->role === 'admin'){
            $loginUrl = "/admin-login";
        }else if($user->role === 'superadmin'){
            $loginUrl = "/superadmin-login";
        } else {
            $loginUrl = "/". $request->registration_method;
        }
    
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
            'loginUrl' => $loginUrl,
        ]);
    }
}
