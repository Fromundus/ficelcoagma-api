<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index(){
        $setting = Setting::first();

        if($setting){
            return response()->json([
                "data" => $setting,
            ], status: 200);
        } else {
            return response()->json([
                "message" => "Setting Not Found"
            ], 404);
        }
    }

    public function settingsOnline(){
        $setting = Setting::first();

        if($setting){
            return response()->json([
                "data" => $setting->online,
            ], status: 200);
        } else {
            return response()->json([
                "message" => "Setting Not Found"
            ], 404);
        }
    }

    public function settingsWithLogs(){
        $setting = Setting::first();

        //IF YOU UPDATE THE TAKE VALUE IN SETTINGS WITH LOGS ALSO UPDATE THE VALUE ON UPDATE METHOD.
        $recentLogs = Log::latest()->take(20)->get();

        if($setting){
            return response()->json([
                "data" => $setting,
                "logs" => $recentLogs,
            ], status: 200);
        } else {
            return response()->json([
                "message" => "Setting Not Found"
            ], 404);
        }
    }

    public function store(){
        $setting = Setting::first();

        if($setting){
            return response()->json([
                "message" => "Settings is already configured.",
            ], status: 422);
        } else {
            $newSetting = Setting::factory(1)->create();

            if($newSetting){
                $updatedSetting = Setting::first();

                return response()->json([
                    "data" => $updatedSetting,
                ], status: 200);
            } else {
                return response()->json([
                    "message" => "Something went wrong.",
                ], status: 500);
            }
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            "status" => "required|string",
            "field" => "required|string",
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => $validator->errors(),
            ], 422);
        } else {
            $setting = Setting::first();

            $user = $request->user();
    
            if($setting){
                if($request->field === 'prereg'){
                    if($request->status === 'active'){
                        $setting->update([
                            $request->field => $request->status,
                            'onsite' => 'inactive'
                        ]);

                        $this->createLog($user, "Activated pre registration & deactivated onsite registration.");

                    } else {
                        $setting->update([
                            $request->field => $request->status,
                            'onsite' => 'active'
                        ]);

                        $this->createLog($user, "Deactivated pre registration & activated onsite registration.");

                    }
                } else if ($request->field === 'onsite'){
                    if($request->status === 'active'){
                        $setting->update([
                            $request->field => $request->status,
                            'prereg' => 'inactive'
                        ]);

                        $this->createLog($user, "Activated onsite registration & deactivated pre registration.");
                    } else {
                        $setting->update([
                            $request->field => $request->status,
                            'prereg' => 'active'
                        ]);

                        $this->createLog($user, "Deactivated onsite registration & activated pre registration.");
                    }
                } else if ($request->field === 'online') {
                    $setting->update([
                        $request->field => $request->status,
                    ]);

                    if($request->status === 'active'){
                        $this->createLog($user, "Activated online registration.");
                    } else {
                        $this->createLog($user, "Deactivated online registration.");
                    }

                }

                if($setting){
                    $updatedSetting = Setting::first();
                    $recentLogs = Log::latest()->take(20)->get();

                    // if($request->field === "onsite"){
                    //     User::where("role", "ons")->update([
                    //         "status" => $request->status,
                    //     ]);
                    // } else if ($request->field === "prereg"){
                    //     User::where("role", "pre")->update([
                    //         "status" => $request->status,
                    //     ]);
                    // }

                    return response()->json([
                        "message" => "Successfully Updated",
                        "data" => $updatedSetting,
                        "logs" => $recentLogs,
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Something Went Wrong"
                    ], 500);
                }
            } else {
                return response()->json([
                    "message" => "Setting Not Found"
                ], 404);
            }
        }
    }

    private function createLog($user, $description){
        $log = Log::create([
            "user_id" => $user->id,
            "name" => $user->name,
            "fullname" => $user->fullname,
            "description" => $description,
        ]);

        if($log){
            return response()->json([
                "message" => "Successfully Created."
            ], 404);
        } else {
            return response()->json([
                "message" => "Something went wrong."
            ], 500);
        }
    }
}
