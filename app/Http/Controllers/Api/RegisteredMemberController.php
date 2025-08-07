<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RegisteredMemberMail;
use App\Models\Member;
use App\Models\RegisteredMember;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisteredMemberController extends Controller
{
    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $query = RegisteredMember::query();

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('account_number', 'like', "%{$search}%")
    //             ->orWhere('name', 'like', "%{$search}%")
    //             ->orWhere('address', 'like', "%{$search}%")
    //             ->orWhere('occupant', 'like', "%{$search}%")
    //             ->orWhere('registration_method', 'like', "%{$search}%")
    //             ->orWhere('book', 'like', "%{$search}%");
    //         });
    //     }

    //     $data = $query->orderBy('created_at', 'desc')->paginate(10);

    //     return response()->json(['data' => $data], 200);
    // }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = RegisteredMember::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('occupant', 'like', "%{$search}%")
                ->orWhere('registration_method', 'like', "%{$search}%")
                ->orWhere('book', 'like', "%{$search}%");
            });
        }

        // Total stats (unfiltered)
        $onsiteCount = RegisteredMember::where('registration_method', 'onsite')->count();
        $onlineCount = RegisteredMember::where('registration_method', 'online')->count();
        $preRegCount = RegisteredMember::where('registration_method', 'prereg')->count();

        // Paginated, filtered results
        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $data,
            'stats' => [
                'onsite' => $onsiteCount,
                'online' => $onlineCount,
                'prereg' => $preRegCount,
            ]
        ], 200);
    }

    
    public function show($reference_number){
        $registeredMember = RegisteredMember::where("reference_number", $reference_number)->first();

        if($registeredMember){
            return response()->json([
                "data" => $registeredMember
            ], 200);
        } else {
            return response()->json([
                "message" => "Member Not Found"
            ], 404);
        }
    }

    public function showUsingAccountNumber($account_number){
        $registeredMember = RegisteredMember::where("account_number", $account_number)->first();

        if($registeredMember){
            return response()->json([
                "data" => $registeredMember
            ], 200);
        } else {
            return response()->json([
                "message" => "Member Not Found"
            ], 404);
        }
    }


    public function store(Request $request){
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
        $validator = Validator::make($request->all(), [
            "account_number" => "required|string|min:8|max:8",
            "book" => "required|string|min:6|max:6",
            "name" => "required|string|max:50",
            "address" => "required|string",
            "occupant" => "required|string|max:50",
            "id_presented" => "required|string",
            "id_number" => "required|string",
            "phone_number" => "string|min:11|max:11|nullable",
            "email" => "string|email|nullable",
            "created_by" => "string|nullable",
            "status" => "string|nullable",
            // "registration_method" => "string|nullable",
            "role" => "string|nullable"
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => $validator->errors()
            ], 422);
        } else {
            $member = Member::where("account_number", $request->account_number)->where("book", $request->book)->first();
            
            if($member){
                if($member["status"] === "registered"){
                    $registeredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();

                    return response()->json([
                        "message" => "Member is Already Registered",
                        "data" => $registeredMember,
                    ], 201);
                } else {
                    $member->update([
                        "status" => "registered"
                    ]);
                    
                    $uuid = strtoupper(Str::random(8));

                    $registeredMember = RegisteredMember::create([
                        "account_number" => $request->account_number,
                        "book" => $request->book,
                        "name" => $request->name,
                        "address" => $request->address,
                        "occupant" => $request->occupant,
                        "id_presented" => $request->id_presented,
                        "id_number" => $request->id_number,
                        "phone_number" => $request->phone_number,
                        "email" => $request->email,
                        "created_by" => $request->created_by,
                        "status" => $request->status,
                        "reference_number" => $uuid,
                        "registration_method" => $request->registration_method,
                    ]);

                    if($registeredMember){
                        if (!empty($registeredMember->email)) {
                            Mail::to($registeredMember->email)->send(new RegisteredMemberMail($registeredMember));
                        }

                        return response()->json([
                            "message" => "Successfuly Registered",
                            "data" => $registeredMember
                        ], 200);
                    } else {
                        return response()->json([
                            "message" => "Internal Server Error"
                        ], 500);
                    }

                }
            } else {
                return response()->json([
                    "message" => "Member Not Found"
                ], 404);
            }
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            "account_number" => "required|string|min:8|max:8",
            "book" => "required|string|min:6|max:6",
            "name" => "required|string|max:50",
            "address" => "required|string",
            "occupant" => "string|max:50|nullable",
            "id_presented" => "required|string",
            "id_number" => "required|string",
            "phone_number" => "string|min:11|max:11|nullable",
            "email" => "string|email|nullable",
            "createdBy" => "string|nullable",
            "status" => "string|nullable",
        ]);

        if($validator->fails()){
            return response()->json([
                "message" => $validator->errors()
            ], 422);
        } else {
            $registeredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();
            
            if($registeredMember){
                $registeredMember->update([
                    "account_number" => $request->account_number,
                    "book" => $request->book,
                    "name" => $request->name,
                    "address" => $request->address,
                    "occupant" => $request->occupant,
                    "id_presented" => $request->id_presented,
                    "id_number" => $request->id_number,
                    "phone_number" => $request->phone_number,
                    "email" => $request->email,
                    "createdBy" => $request->createdBy,
                    "status" => $request->status,
                    "registration_method" => $request->registration_method,
                ]);

                if($registeredMember){
                    $updatedRegisteredMember = RegisteredMember::where("account_number", $request->account_number)->where("book", $request->book)->first();

                    return response()->json([
                        "message" => "Successfuly Updated",
                        "data" => $updatedRegisteredMember
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Internal Server Error"
                    ], 500);
                }
            } else {
                return response()->json([
                    "message" => "Member Not Found"
                ], 404);
            }
        }
    }


    public function batchDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'message' => 'No account numbers provided.',
            ], 400);
        }

        // Delete from RegisteredMember first
        $deleted = RegisteredMember::whereIn('account_number', $ids)->delete();

        if ($deleted > 0) {
            // Set 'status' to null in Member model
            Member::whereIn('account_number', $ids)->update(['status' => null]);

            // Return updated list of registered members

            // $updatedRegisteredMembers = RegisteredMember::all();


            return response()->json([
                'message' => 'Deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'No matching registered members found to delete.',
            ], 404);
        }
    }

    public function exportPdf()
    {
        $members = RegisteredMember::all();

        $pdf = Pdf::loadView('exports.registered_members_pdf', ['members' => $members]);

        return $pdf->download('registered_members.pdf');
    }

    public function exportSql()
    {
        $table = 'registered_members';
        $members = DB::table($table)->get();

        $sql = "INSERT INTO `$table` (`id`, `account_number`, `book`, `name`, `address`, `occupant`, `id_presented`, `id_number`, `phone_number`, `email`, `created_by`, `status`, `reference_number`, `registration_method`, `created_at`, `updated_at`) VALUES\n";

        $values = [];
        foreach ($members as $member) {
            $escaped = array_map(function ($value) {
                return is_null($value) ? 'NULL' : "'" . str_replace("'", "''", $value) . "'";
            }, (array) $member);
            $values[] = "(" . implode(", ", $escaped) . ")";
        }

        $sql .= implode(",\n", $values) . ";\n";

        return Response::make($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="registered_members.sql"',
        ]);
    }

}
