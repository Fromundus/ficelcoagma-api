<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['data' => $data], 200);
    }

    public function changePassword(Request $request, $id){
        $user = User::where("id", $id)->first();

        if($user){
            $validator = Validator::make($request->all(), [
                "password" => "required|confirmed|string|min:6"
            ]);

            if($validator->fails()){
                return response()->json([
                    "status" => "422",
                    "message" => $validator->errors()
                ], 422);
            } else {
                $user->update([
                    "password" => Hash::make($request->password)
                ]);

                if($user){                    
                    return response()->json([
                        "status" => "200",
                        "message" => "Password Updated Successfully"
                    ], 200);
                } else {
                    return response()->json([
                        "status" => "500",
                        "message" => "Something Went Wrong"
                    ]);
                }
            }
        } else {
            return response()->json([
                "status" => "404",
                "message" => "User Not Found"
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::where("id", $id)->first();

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:50|unique:users,name,' . $user->id ,
                'fullname' => 'required|string',
                'role' => 'required|string',
                // 'email' => 'required|email|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => $validator->errors()
                ], 422);
            } else {
                $user->update([
                    "name" => $request->name,
                    "fullname" => $request->fullname,
                    "role" => $request->role,
                    // "email" => $request->email,
                ]);

                return response()->json([
                    "status" => "200",
                    "message" => "Account Updated Successfully",
                    // "user" => $user,
                ], 200);
            }
        } else {
            return response()->json([
                "status" => "404",
                "message" => "User not found"
            ], 404);
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
        $deleted = User::whereIn('id', $ids)->delete();

        if ($deleted > 0) {
            return response()->json([
                'message' => 'Deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'No matching registered members found to delete.',
            ], 404);
        }
    }
}
