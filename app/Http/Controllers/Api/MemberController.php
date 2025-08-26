<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\RegisteredMember;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{

    // public function getStats(Request $request)
    // {
    //     $totalMembers = Member::count();
    //     $registeredMembers = RegisteredMember::count();
    //     $unregisteredMembers = $totalMembers - $registeredMembers;

    //     $latestRegistrations = RegisteredMember::latest()->take(5)->get(); // optional: recent 5

    //     return response()->json([
    //         'total_members' => $totalMembers,
    //         'registered_members' => $registeredMembers,
    //         'unregistered_members' => $unregisteredMembers,
    //         'recent_registrations' => $latestRegistrations,
    //     ]);
    // }

    public function getStats(Request $request)
    {
        $totalMembers = Member::count();
        $registeredMembers = RegisteredMember::count();
        $unregisteredMembers = $totalMembers - $registeredMembers;

        $latestRegistrations = RegisteredMember::latest()->take(5)->get();

        // Daily registrations for the last 14 days
        $daily = RegisteredMember::select(
            DB::raw("DATE(created_at) as date"),
            DB::raw("COUNT(*) as count")
        )
        ->where('created_at', '>=', now()->subDays(13))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        // Monthly registrations for current year
        $monthly = RegisteredMember::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("COUNT(*) as count")
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        $totalRegistered = RegisteredMember::count();
        $onsiteCount = RegisteredMember::where('registration_method', 'onsite')->count();
        $onlineCount = RegisteredMember::where('registration_method', 'online')->count();
        $preRegCount = RegisteredMember::where('registration_method', 'prereg')->count();

        $areaNames = [
            "001" => "Virac",
            "002" => "Bato",
            "003" => "San Andres",
            "004" => "San Miguel",
            "005" => "Baras",
            "006" => "Gigmoto",
            "007" => "Viga",
            "008" => "Panganiban",
            "009" => "Bagamanoc",
            "010" => "Caramoran",
            "011" => "Pandan",
        ];

        $areaStats = RegisteredMember::select(
            'area',
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('area')
        ->get()
        ->map(function ($row) use ($areaNames) {
            return [
                'area' => $areaNames[$row->area] ?? $row->area,
                'count' => $row->count,
            ];
        });

        return response()->json([
            'total_members' => $totalMembers,
            'registered_members' => $registeredMembers,
            'unregistered_members' => $unregisteredMembers,
            'recent_registrations' => $latestRegistrations,
            'daily_registrations' => $daily,
            'monthly_registrations' => $monthly,
            'total_registered_members' => $totalRegistered,
            'online' => $onlineCount,
            'prereg' => $preRegCount,
            'onsite' => $onsiteCount,
            'area_stats' => $areaStats,
        ]);
    }


    public function validate(Request $request){
        $setting = Setting::first();

        if(!$setting){
            return response()->json([
                "message" => "Settings is not configured yet. Please try again later."
            ], status: 404);
        }

        $user = $request->user();

        if(!$user){
            if($request->registration_method === "online" && $setting->online !== "active"){
                return response()->json([
                    "message" => "Online registration is closed."
                ], 404);
            }
        } else {
            if($user->role === 'user'){
                if ($request->registration_method === "prereg" && $setting->prereg !== "active"){
                    return response()->json([
                        "message" => "Pre registration is closed."
                    ], 401);
                } else if ($request->registration_method === "onsite" && $setting->onsite !== "active"){
                    return response()->json([
                        "message" => "Onsite registration is closed."
                    ], 401);
                }
            }
        }

        //THIS IS FOR ALL REGISTRATION IF THE SETTINGS IS INACTIVE IT SHOULD NOT ALLOW TO REGISTER
        // if($request->registration_method === "online" && $setting->online !== "active"){
        //     return response()->json([
        //         "message" => "Online registration is closed."
        //     ], 404);
        // } else if ($request->registration_method === "prereg" && $setting->prereg !== "active"){
        //     return response()->json([
        //         "message" => "Pre registration is closed."
        //     ], 401);
        // } else if ($request->registration_method === "onsite" && $setting->onsite !== "active"){
        //     return response()->json([
        //         "message" => "Onsite registration is closed."
        //     ], 401);
        // }

        // THIS IS FOR REGISTRATION OF ALL LOGGED IN USER, THE REQUEST TO REGISTER IS HANDLED BY THE ENSUREISACTIVE MIDDLEWARE

        if($request->registration_method === "online"){
            $validator = Validator::make($request->all(), [
                "account_number" => "required|string|min:8|max:8",
                "book" => "required|string|min:6|max:6",
            ]);
        } else if ($request->registration_method === "prereg" || $request->registration_method === "onsite"){
            $validator = Validator::make($request->all(), [
                "account_number" => "required|string|min:8|max:8",
            ]);
        }


        if($validator->fails()){
            return response()->json([
                "message" => $validator->errors()
            ], 422);
        } else {
            $member = null;

            // i removed book from prereg and onsite
            if($request->registration_method === "online"){
                $member = Member::where("account_number", $request->account_number)->where("book", $request->book)->first();
            } else if ($request->registration_method === "prereg" || $request->registration_method === "onsite"){
                $member = Member::where("account_number", $request->account_number)->first();
            }

            // $member = Member::where("account_number", $request->account_number)->where("book", $request->book)->first();

            if($member){
                if($member["status"] === "registered"){
                    $registeredMember = null;

                    // i removed book from prereg and onsite
                    if($request->registration_method === "online"){
                        $registeredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();
                    } else if ($request->registration_method === "prereg" || $request->registration_method === "onsite"){
                        $registeredMember = RegisteredMember::where("account_number", $request->account_number)->first();
                    }

                    // $registeredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();

                    // if($request->registration_method === "onsite"){
                    //     $registeredMember->update([
                    //         "registration_method" => $request->registration_method,
                    //     ]);
                    // }

                    return response()->json([
                        "message" => "Member is Already Registered",
                        "data" => $registeredMember,
                    ], 201);
                }

                return response()->json([
                    "message" => "Successfuly Validated",
                    "data" => $member
                ], 200);
            } else {
                return response()->json([
                    "message" => "Member Not Found"
                ], 404);
            }
        }
    }

    public function confirmUpdateOnsite(Request $request){
        $validator = Validator::make($request->all(), [
            "account_number" => "required|string|min:8|max:8",
            // "book" => "required|string|min:6|max:6",
            "registration_method" => "required|string",
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => $validator->errors()
            ], 422);
        } else {
            // $registeredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();

            $registeredMember = RegisteredMember::where("account_number", $request->account_number)->first();

            if($registeredMember){
                $registeredMember->update([
                    "registration_method" => $request->registration_method,
                ]);

                return response()->json([
                    "message" => "Registration Method Updated.",
                    "data" => $registeredMember,
                ], 200);
            } else {
                return response()->json([
                    "message" => "Member Not Found"
                ], 404);
            }
        }
    }
}
