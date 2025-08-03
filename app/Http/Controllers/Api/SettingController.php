<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    
            if($setting){
                if($request->field === 'prereg'){
                    if($request->status === 'active'){
                        $setting->update([
                            $request->field => $request->status,
                            'onsite' => 'inactive'
                        ]);
                    } else {
                        $setting->update([
                            $request->field => $request->status,
                            'onsite' => 'active'
                        ]);
                    }
                } else if ($request->field === 'onsite'){
                    if($request->status === 'active'){
                        $setting->update([
                            $request->field => $request->status,
                            'prereg' => 'inactive'
                        ]);
                    } else {
                        $setting->update([
                            $request->field => $request->status,
                            'prereg' => 'active'
                        ]);
                    }
                } else {
                    $setting->update([
                        $request->field => $request->status,
                    ]);
                }

                if($setting){
                    $updatedSetting = Setting::first();

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
}
