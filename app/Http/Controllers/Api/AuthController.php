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
            // 'email' => 'required|email|unique:users',
            // 'password' => 'required|confirmed|string|min:6',
            'role' => 'required|string',
        ]);

        $setting = Setting::first();

        $status = "";

        if($setting){
            if($request->role === "pre"){
                if($setting->prereg === "active"){
                    $status = "active";
                } else {
                    $status = "inactive";
                }
            } else if($request->role === "ons"){
                if($setting->onsite === "active"){
                    $status = "active";
                } else {
                    $status = "inactive";
                }
            } else {
                $status = "active";
            }
        } else {
            $status = "inactive";
        }

        $user = User::create([
            'name' => $data['name'],
            // 'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make(123456),
            'status' => $status,
        ]);

        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required',
            'required_role' => 'required',
        ]);

        $user = User::where('name', $credentials['name'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (($user && $user->status !== "active") || ($request->required_role != $user->role)) {
            throw ValidationException::withMessages([
                'name' => ['Invalid Account.'],
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
